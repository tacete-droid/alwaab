<?php

namespace Database\Seeders;

use App\Domain\CRM\Models\Contact;
use App\Domain\FieldVisit\Models\FieldVisit;
use App\Domain\Inventory\Models\Product;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\FieldVisitStatus;
use App\Enums\RfqStatus;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Database\Seeder;

class QuotationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $salesRep = User::where('email', 'sales@alwaab.ae')->first();
        $fieldOfficer = User::where('email', 'field@alwaab.ae')->first();
        $customer = Contact::where('type', 'customer')->first();
        $project = Project::first();

        if (! $customer || ! $salesRep) {
            return;
        }

        $quotationService = app(QuotationService::class);

        $rfq = Rfq::firstOrCreate(
            ['number' => 'RFQ-'.now()->year.'-001'],
            [
                'contact_id' => $customer->id,
                'project_id' => $project?->id,
                'status' => RfqStatus::Pending,
                'description' => 'طلب تسعير أنابيب CPVC FlowGuard لمشروع دبي مارينا',
                'assigned_to' => $salesRep->id,
            ]
        );

        $products = Product::orderBy('source_sno')->limit(3)->get();
        if ($products->isNotEmpty() && ! \App\Domain\Quotations\Models\Quotation::exists()) {
            $quotationService->createQuotation([
                'contact_id' => $customer->id,
                'project_id' => $project?->id,
                'rfq_id' => $rfq->id,
                'discount_aed' => 0,
                'notes' => 'عرض سعر تجريبي — FlowGuard CPVC',
                'items' => $products->map(fn (Product $p) => [
                    'product_id' => $p->id,
                    'quantity' => $p->type->value === 'pipe' ? 100 : 50,
                ])->all(),
            ], $salesRep);
        }

        if ($project && $fieldOfficer && ! FieldVisit::exists()) {
            FieldVisit::create([
                'project_id' => $project->id,
                'employee_id' => $fieldOfficer->id,
                'lat' => $project->lat,
                'lng' => $project->lng,
                'visited_at' => now()->subDays(2),
                'completed_at' => now()->subDays(2)->addHours(2),
                'status' => FieldVisitStatus::Completed,
                'notes' => 'زيارة ميدانية لتقييم متطلبات الأنابيب في الموقع',
            ]);

            FieldVisit::create([
                'project_id' => $project->id,
                'employee_id' => $fieldOfficer->id,
                'lat' => $project->lat,
                'lng' => $project->lng,
                'visited_at' => now()->subHours(3),
                'status' => FieldVisitStatus::InProgress,
                'notes' => 'زيارة جارية — متابعة التركيب',
            ]);
        }
    }
}
