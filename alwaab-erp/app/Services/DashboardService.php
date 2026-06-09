<?php

namespace App\Services;

use App\Domain\CRM\Models\Contact;
use App\Domain\CRM\Models\Lead;
use App\Domain\FieldVisit\Models\FieldVisit;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Quotation;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKpis(?User $user = null): array
    {
        $year = now()->year;

        return [
            'sales' => [
                'ytd_aed' => (float) Quotation::where('status', QuotationStatus::Approved)
                    ->whereYear('approved_at', $year)
                    ->sum('total_aed'),
                'month_target_aed' => 500000,
                'month_actual_aed' => (float) Quotation::where('status', QuotationStatus::Approved)
                    ->whereMonth('approved_at', now()->month)
                    ->whereYear('approved_at', $year)
                    ->sum('total_aed'),
            ],
            'projects' => [
                'active' => Project::where('status', ProjectStatus::Active)->count(),
                'total_value_aed' => (float) Project::where('status', ProjectStatus::Active)->sum('value_aed'),
            ],
            'crm' => [
                'contacts' => Contact::count(),
                'leads_pipeline' => Lead::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'won_leads' => Lead::where('status', LeadStatus::Won)->count(),
            ],
            'inventory' => [
                'low_stock_items' => Inventory::whereColumn('qty_on_hand', '<=', 'reorder_point')->count(),
                'total_skus' => Inventory::distinct('product_id')->count('product_id'),
            ],
            'field_visits' => [
                'today' => FieldVisit::whereDate('visited_at', today())->count(),
                'this_month' => FieldVisit::whereMonth('visited_at', now()->month)->count(),
            ],
            'users' => [
                'active' => User::where('is_active', true)->count(),
                'last_logins' => User::whereNotNull('last_login_at')
                    ->orderByDesc('last_login_at')
                    ->limit(5)
                    ->get(['id', 'name_ar', 'name_en', 'last_login_at']),
            ],
        ];
    }
}
