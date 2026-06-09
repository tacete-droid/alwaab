<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->string('type'); // quotation, sales, proforma
            $table->string('status')->default('draft');
            $table->date('document_date');
            $table->foreignUuid('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUuid('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->string('attention_to')->nullable();
            $table->string('project_name')->nullable();
            $table->string('consultant')->nullable();
            $table->string('main_contractor')->nullable();
            $table->string('mep_contractor')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('lpo_number')->nullable();
            $table->text('address')->nullable();
            $table->decimal('subtotal_aed', 15, 2)->default(0);
            $table->decimal('discount_aed', 15, 2)->default(0);
            $table->decimal('vat_aed', 15, 2)->default(0);
            $table->decimal('total_aed', 15, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('document_date');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price_aed', 12, 2)->default(0);
            $table->decimal('total_aed', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
