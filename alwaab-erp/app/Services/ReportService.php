<?php

namespace App\Services;

use App\Domain\CRM\Models\Lead;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\Product;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Quotation;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function salesReport(): array
    {
        $year = now()->year;

        $monthly = Quotation::query()
            ->where('status', QuotationStatus::Approved)
            ->whereYear('approved_at', $year)
            ->select(DB::raw('EXTRACT(MONTH FROM approved_at) as month'), DB::raw('SUM(total_aed) as total'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM approved_at)'))
            ->orderBy('month')
            ->pluck('total', 'month');

        return [
            'ytd_total' => (float) Quotation::where('status', QuotationStatus::Approved)
                ->whereYear('approved_at', $year)->sum('total_aed'),
            'approved_count' => Quotation::where('status', QuotationStatus::Approved)->count(),
            'pending_count' => Quotation::whereIn('status', [QuotationStatus::Draft, QuotationStatus::Sent])->count(),
            'monthly' => collect(range(1, 12))->map(fn ($m) => [
                'month' => $m,
                'total' => (float) ($monthly[$m] ?? 0),
            ]),
        ];
    }

    public function projectsReport(): array
    {
        return [
            'by_status' => Project::select('status', DB::raw('count(*) as count'), DB::raw('SUM(value_aed) as value'))
                ->groupBy('status')
                ->get()
                ->map(fn ($r) => [
                    'status' => $r->status,
                    'count' => $r->count,
                    'value' => (float) $r->value,
                ]),
            'top_projects' => Project::orderByDesc('value_aed')->limit(5)
                ->get(['id', 'name_ar', 'name_en', 'status', 'value_aed', 'location']),
        ];
    }

    public function inventoryReport(): array
    {
        $lowStock = Inventory::with('product:id,sku,name_ar,name_en')
            ->whereColumn('qty_on_hand', '<=', 'reorder_point')
            ->orderBy('qty_on_hand')
            ->limit(10)
            ->get();

        return [
            'total_skus' => Product::where('is_active', true)->count(),
            'low_stock_count' => Inventory::whereColumn('qty_on_hand', '<=', 'reorder_point')->count(),
            'total_value' => (float) Inventory::query()
                ->join('products', 'inventory.product_id', '=', 'products.id')
                ->selectRaw('SUM(inventory.qty_on_hand * products.price_aed) as val')
                ->value('val'),
            'low_stock_items' => $lowStock,
        ];
    }

    public function crmReport(): array
    {
        return [
            'leads_by_status' => Lead::select('status', DB::raw('count(*) as count'), DB::raw('SUM(value_aed) as value'))
                ->groupBy('status')
                ->get(),
            'won_value' => (float) Lead::where('status', LeadStatus::Won)->sum('value_aed'),
            'active_projects' => Project::where('status', ProjectStatus::Active)->count(),
        ];
    }
}
