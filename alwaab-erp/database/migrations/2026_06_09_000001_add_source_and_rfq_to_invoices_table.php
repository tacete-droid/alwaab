<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('source')->default('internal')->after('status');
            $table->foreignUuid('rfq_id')->nullable()->after('quotation_id')->constrained('rfqs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rfq_id');
            $table->dropColumn('source');
        });
    }
};
