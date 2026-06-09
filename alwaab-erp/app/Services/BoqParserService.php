<?php

namespace App\Services;

use App\Domain\Inventory\Models\Product;
use App\Domain\Quotations\Models\Rfq;
use App\Domain\Quotations\Models\RfqBoqItem;
use App\Enums\RfqStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BoqParserService
{
    private const HEADER_KEYWORDS = [
        'description' => ['material', 'description', 'item', 'product', 'flowguard', 'cpvc'],
        'client_enquiry' => ['client enquiry', 'client_enq', 'enquiry', 'sku', 'code', 'ref'],
        'quantity' => ['qty', 'quantity', 'qnty', 'q.ty'],
        'unit' => ['unit', 'uom'],
        'unit_price' => ['unit price', 'unit_price', 'rate', 'price/unit'],
        'total' => ['total', 'amount', 'price'],
    ];

    public function parseAndStore(Rfq $rfq, UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            "boq/{$rfq->id}",
            now()->format('Ymd_His').'_'.$file->getClientOriginalName(),
            'public',
        );

        $fullPath = Storage::disk('public')->path($storedPath);
        $rows = match ($extension) {
            'csv' => $this->parseCsv($fullPath),
            'xlsx', 'xls' => $this->parseSpreadsheet($fullPath),
            default => throw new \InvalidArgumentException(__('quotations.boq_unsupported_format')),
        };

        return DB::transaction(function () use ($rfq, $storedPath, $rows) {
            $rfq->boqItems()->delete();

            $sortOrder = 0;
            $currentCategory = null;
            $matched = 0;
            $warnings = [];

            foreach ($rows as $row) {
                if ($row['is_category'] ?? false) {
                    $currentCategory = $row['description'];
                    continue;
                }

                $product = $this->matchProduct($row['description'], $row['client_enquiry'] ?? null);
                $unitPrice = $row['unit_price'] ?? ($product?->price_aed);
                $quantity = max(1, (int) ($row['quantity'] ?? 1));
                $total = $row['total'] ?? ($unitPrice !== null ? round($quantity * (float) $unitPrice, 2) : null);

                if ($product) {
                    $matched++;
                } elseif (! empty($row['client_enquiry'])) {
                    $warnings[] = __('quotations.boq_product_not_found', ['sku' => $row['client_enquiry']]);
                }

                RfqBoqItem::create([
                    'rfq_id' => $rfq->id,
                    'row_number' => $row['row_number'] ?? 0,
                    'description' => $row['description'],
                    'client_enquiry' => $row['client_enquiry'] ?? null,
                    'quantity' => $quantity,
                    'unit' => $row['unit'] ?? ($product?->unit ?? 'pcs'),
                    'unit_price_aed' => $unitPrice,
                    'total_aed' => $total,
                    'product_id' => $product?->id,
                    'category' => $currentCategory,
                    'sort_order' => ++$sortOrder,
                ]);
            }

            $rfq->update([
                'boq_file_url' => Storage::disk('public')->url($storedPath),
                'status' => RfqStatus::Processing,
            ]);

            return [
                'items_count' => $sortOrder,
                'matched_count' => $matched,
                'warnings' => array_slice(array_unique($warnings), 0, 10),
            ];
        });
    }

    private function parseSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        [$headerRowIndex, $columns] = $this->detectColumns($matrix);

        if ($headerRowIndex === null) {
            throw new \InvalidArgumentException(__('quotations.boq_header_not_found'));
        }

        return $this->extractRows($matrix, $headerRowIndex, $columns);
    }

    private function parseCsv(string $path): array
    {
        $matrix = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException(__('quotations.boq_read_failed'));
        }

        while (($line = fgetcsv($handle)) !== false) {
            $matrix[] = $line;
        }

        fclose($handle);

        [$headerRowIndex, $columns] = $this->detectColumns($matrix);

        if ($headerRowIndex === null) {
            throw new \InvalidArgumentException(__('quotations.boq_header_not_found'));
        }

        return $this->extractRows($matrix, $headerRowIndex, $columns);
    }

    private function detectColumns(array $matrix): array
    {
        $limit = min(count($matrix), 40);

        for ($i = 0; $i < $limit; $i++) {
            $row = array_map(fn ($cell) => $this->normalizeHeader((string) $cell), $matrix[$i] ?? []);
            $columns = $this->mapHeaderColumns($row);

            if (isset($columns['description']) && (isset($columns['quantity']) || isset($columns['client_enquiry']))) {
                return [$i, $columns];
            }
        }

        return [null, []];
    }

    private function mapHeaderColumns(array $row): array
    {
        $columns = [];

        foreach ($row as $index => $cell) {
            if ($cell === '') {
                continue;
            }

            foreach (self::HEADER_KEYWORDS as $field => $keywords) {
                if (isset($columns[$field])) {
                    continue;
                }

                foreach ($keywords as $keyword) {
                    if (str_contains($cell, $keyword)) {
                        $columns[$field] = $index;
                        break;
                    }
                }
            }

            if (preg_match('/^s\.?\s*no\.?$/i', $cell)) {
                $columns['serial'] = $index;
            }
        }

        return $columns;
    }

    private function extractRows(array $matrix, int $headerRowIndex, array $columns): array
    {
        $rows = [];

        for ($i = $headerRowIndex + 1; $i < count($matrix); $i++) {
            $line = $matrix[$i] ?? [];
            $rowNumber = $i + 1;

            $description = trim((string) ($line[$columns['description']] ?? ''));
            $clientEnquiry = trim((string) ($line[$columns['client_enquiry'] ?? -1] ?? ''));
            $qtyRaw = $line[$columns['quantity'] ?? -1] ?? null;
            $unit = trim((string) ($line[$columns['unit'] ?? -1] ?? ''));
            $unitPrice = $this->parseNumber($line[$columns['unit_price'] ?? -1] ?? null);
            $total = $this->parseNumber($line[$columns['total'] ?? -1] ?? null);
            $serial = trim((string) ($line[$columns['serial'] ?? -1] ?? ''));

            if ($description === '' && $clientEnquiry === '') {
                continue;
            }

            $quantity = $this->parseQuantity($qtyRaw);

            if ($this->isCategoryRow($description, $clientEnquiry, $quantity, $serial)) {
                $rows[] = [
                    'row_number' => $rowNumber,
                    'description' => $description,
                    'is_category' => true,
                ];
                continue;
            }

            if ($description === '' || $this->isSkippedRow($description, $clientEnquiry, $quantity)) {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'description' => $description,
                'client_enquiry' => $clientEnquiry !== '' ? $clientEnquiry : null,
                'quantity' => $quantity,
                'unit' => $unit !== '' ? $unit : 'pcs',
                'unit_price' => $unitPrice,
                'total' => $total,
            ];
        }

        return $rows;
    }

    private function isCategoryRow(string $description, string $clientEnquiry, ?int $quantity, string $serial): bool
    {
        if ($clientEnquiry !== '' || $quantity !== null) {
            return false;
        }

        if ($serial !== '' && is_numeric($serial)) {
            return false;
        }

        return strlen($description) > 0 && ! preg_match('/^\d/', $description);
    }

    private function isSkippedRow(string $description, string $clientEnquiry, ?int $quantity): bool
    {
        if (strtoupper($clientEnquiry) === 'N/A' && ($quantity === null || $quantity === 0)) {
            return true;
        }

        if (preg_match('/^(total|sub\s*total|grand\s*total|remarks?|note)/i', $description)) {
            return true;
        }

        return false;
    }

    private function matchProduct(string $description, ?string $clientEnquiry): ?Product
    {
        if ($clientEnquiry) {
            $bySku = Product::where('sku', $clientEnquiry)->where('is_active', true)->first();
            if ($bySku) {
                return $bySku;
            }

            $bySource = Product::where('source_sno', $clientEnquiry)->where('is_active', true)->first();
            if ($bySource) {
                return $bySource;
            }
        }

        $normalized = $this->normalizeHeader($description);

        return Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($normalized, $description) {
                $q->whereRaw('LOWER(name_en) = ?', [strtolower($description)])
                    ->orWhereRaw('LOWER(name_ar) = ?', [strtolower($description)])
                    ->orWhereRaw('LOWER(name_en) LIKE ?', ['%'.$normalized.'%']);
            })
            ->first();
    }

    private function normalizeHeader(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
    }

    private function parseQuantity(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return max(0, (int) round((float) $value));
        }

        $cleaned = preg_replace('/[^0-9.]/', '', (string) $value);

        return $cleaned !== '' ? max(0, (int) round((float) $cleaned)) : null;
    }

    private function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return $cleaned !== '' && is_numeric($cleaned) ? round((float) $cleaned, 2) : null;
    }
}
