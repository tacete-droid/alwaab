<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_boq_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->unsignedInteger('row_number')->default(0);
            $table->string('description');
            $table->string('client_enquiry')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price_aed', 12, 2)->nullable();
            $table->decimal('total_aed', 15, 2)->nullable();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('category')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rfq_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_boq_items');
    }
};
