<?php

namespace App\Services;

use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductCategory;
use App\Domain\Inventory\Models\Warehouse;
use App\Enums\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FlowGuardCatalogImporter
{
    public function import(?string $jsonPath = null, bool $replaceDemo = true): array
    {
        $path = $jsonPath ?? database_path('data/flowguard_price_list.json');

        if (! File::exists($path)) {
            throw new \RuntimeException("Catalog file not found: {$path}");
        }

        $items = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        return DB::transaction(function () use ($items, $replaceDemo) {
            if ($replaceDemo) {
                $this->removeDemoProducts();
            }

            $categories = $this->ensureCategories();
            $warehouse = $this->ensureWarehouse();

            $created = 0;
            $updated = 0;

            foreach ($items as $item) {
                $category = $categories[$item['section_slug']] ?? $categories['hot-water'];

                $product = Product::withTrashed()->firstOrNew(['sku' => $item['sku']]);
                $wasNew = ! $product->exists;

                $product->fill([
                    'source_sno' => $item['source_sno'],
                    'name_ar' => $item['name_ar'],
                    'name_en' => $item['name_en'],
                    'category_id' => $category->id,
                    'type' => ProductType::from($item['type']),
                    'fitting_type' => $item['fitting_type'] ?? null,
                    'size' => $item['size'],
                    'pressure_rating' => $item['pressure_rating'],
                    'unit' => $item['unit'] ?? 'pcs',
                    'price_aed' => $item['price_aed'],
                    'price_with_markup_aed' => $item['price_with_markup_aed'] ?? null,
                    'certifications' => $item['certifications'] ?? ['FM', 'NSF', 'UL'],
                    'is_active' => true,
                ]);

                if ($product->trashed()) {
                    $product->restore();
                } else {
                    $product->save();
                }

                if ($wasNew) {
                    $created++;
                } else {
                    $updated++;
                }

                Inventory::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'qty_on_hand' => 0,
                        'qty_reserved' => 0,
                        'reorder_point' => 50,
                        'unit' => $item['unit'] ?? 'pcs',
                    ]
                );
            }

            return [
                'total' => count($items),
                'created' => $created,
                'updated' => $updated,
            ];
        });
    }

    private function removeDemoProducts(): void
    {
        $demoSkus = ['FG-P-20', 'FG-P-25', 'FG-F-20', 'FG-V-25'];

        Product::whereIn('sku', $demoSkus)->each(function (Product $product) {
            Inventory::where('product_id', $product->id)->delete();
            $product->forceDelete();
        });
    }

    /** @return array<string, ProductCategory> */
    private function ensureCategories(): array
    {
        $parent = ProductCategory::firstOrCreate(
            ['slug' => 'flowguard'],
            [
                'name_ar' => 'فلوغارد',
                'name_en' => 'FlowGuard',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $cold = ProductCategory::firstOrCreate(
            ['slug' => 'cold-water'],
            [
                'name_ar' => 'مياه باردة',
                'name_en' => 'Cold Water',
                'parent_id' => $parent->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $hot = ProductCategory::firstOrCreate(
            ['slug' => 'hot-water'],
            [
                'name_ar' => 'مياه ساخنة',
                'name_en' => 'Hot Water',
                'parent_id' => $parent->id,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        return [
            'cold-water' => $cold,
            'hot-water' => $hot,
        ];
    }

    private function ensureWarehouse(): Warehouse
    {
        return Warehouse::firstOrCreate(
            ['name_en' => 'Main Warehouse - Dubai'],
            [
                'name_ar' => 'المستودع الرئيسي - دبي',
                'location' => 'Jebel Ali, Dubai',
                'lat' => 25.0053,
                'lng' => 55.0760,
            ]
        );
    }
}
