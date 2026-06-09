<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('emirates_id', 20)->nullable()->after('emergency_contact');
            $table->date('emirates_id_expiry')->nullable()->after('emirates_id');
            $table->string('passport_number', 30)->nullable()->after('emirates_id_expiry');
            $table->date('passport_expiry')->nullable()->after('passport_number');
            $table->string('residency_number', 30)->nullable()->after('passport_expiry');
            $table->date('residency_expiry')->nullable()->after('residency_number');
            $table->decimal('basic_salary', 12, 2)->nullable()->after('salary_aed');
            $table->decimal('housing_allowance', 12, 2)->nullable()->after('basic_salary');
            $table->string('iban', 34)->nullable()->after('housing_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'emirates_id',
                'emirates_id_expiry',
                'passport_number',
                'passport_expiry',
                'residency_number',
                'residency_expiry',
                'basic_salary',
                'housing_allowance',
                'iban',
            ]);
        });
    }
};
