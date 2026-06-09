<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'sales');

        return Inertia::render('Reports/Index', [
            'tab' => $tab,
            'sales' => $this->reportService->salesReport(),
            'projects' => $this->reportService->projectsReport(),
            'inventory' => $this->reportService->inventoryReport(),
            'crm' => $this->reportService->crmReport(),
        ]);
    }
}
