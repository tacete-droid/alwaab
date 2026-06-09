<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('pressure_rating');
            $table->string('fitting_type')->nullable()->after('type');
            $table->unsignedInteger('source_sno')->nullable()->after('sku');
            $table->decimal('price_with_markup_aed', 12, 2)->nullable()->after('price_aed');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['unit', 'fitting_type', 'source_sno', 'price_with_markup_aed']);
        });
    }
};
