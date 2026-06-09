<?php

namespace Database\Seeders;

use App\Domain\CRM\Models\Contact;
use App\Domain\CRM\Models\Lead;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Projects\Models\Project;
use App\Enums\ContactStatus;
use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $salesRep = User::where('email', 'sales@alwaab.ae')->first();

        $consultant = Contact::create([
            'type' => ContactType::Consultant,
            'name_ar' => 'مكتب الهندسة الاستشارية',
            'name_en' => 'Engineering Consultancy Office',
            'company' => 'ECO Consultants',
            'email' => 'info@eco.ae',
            'phone' => '+97141234567',
            'emirate' => 'Dubai',
            'status' => ContactStatus::Active,
            'assigned_to' => $salesRep?->id,
        ]);

        $contractor = Contact::create([
            'type' => ContactType::Contractor,
            'name_ar' => 'شركة المقاولات المتحدة',
            'name_en' => 'United Contracting LLC',
            'company' => 'United Contracting',
            'email' => 'projects@united.ae',
            'phone' => '+97142345678',
            'emirate' => 'Abu Dhabi',
            'status' => ContactStatus::Active,
            'assigned_to' => $salesRep?->id,
        ]);

        $customer = Contact::create([
            'type' => ContactType::Customer,
            'name_ar' => 'مطور العقارات الذهبي',
            'name_en' => 'Golden Real Estate Developer',
            'company' => 'Golden Developments',
            'email' => 'procurement@golden.ae',
            'phone' => '+97143456789',
            'emirate' => 'Sharjah',
            'status' => ContactStatus::Active,
            'assigned_to' => $salesRep?->id,
        ]);

        Lead::create([
            'contact_id' => $customer->id,
            'source' => LeadSource::Referral,
            'status' => LeadStatus::Proposal,
            'value_aed' => 2500000,
            'probability' => 70,
            'expected_close' => now()->addMonths(2),
            'assigned_to' => $salesRep?->id,
        ]);

        Project::create([
            'name_ar' => 'برج السكني - دبي مارينا',
            'name_en' => 'Residential Tower - Dubai Marina',
            'location' => 'Dubai Marina, UAE',
            'lat' => 25.0805,
            'lng' => 55.1403,
            'status' => ProjectStatus::Active,
            'value_aed' => 5000000,
            'start_date' => now()->subMonths(3),
            'consultant_id' => $consultant->id,
            'contractor_id' => $contractor->id,
            'assigned_to' => $salesRep?->id,
        ]);

        Warehouse::firstOrCreate(
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
