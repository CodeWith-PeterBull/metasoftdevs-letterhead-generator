<?php

/**
 * Migration for creating the invoice_items table
 *
 * This table stores individual line items for each invoice,
 * including service descriptions, quantities, and amounts.
 *
 * @author Your Name
 *
 * @version 1.0
 *
 * @since Laravel 12
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the invoice_items table with all necessary fields
     * for storing individual invoice line items.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Invoice Reference
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade')
                ->comment('Reference to parent invoice');

            // Item Information
            $table->string('service_name')->comment('Name of service (e.g., Website Maintenance)');
            $table->text('description')->comment('Detailed description of the service provided');
            $table->string('period')->nullable()->comment('Service period (e.g., April 2025, May 2025)');

            // Quantity and Pricing
            $table->decimal('quantity', 10, 2)->default(1)->comment('Quantity of service/product');
            $table->string('unit')->default('service')->comment('Unit of measurement (service, hours, pieces, etc.)');
            $table->decimal('unit_price', 15, 2)->comment('Price per unit');
            $table->decimal('line_total', 15, 2)->comment('Total for this line item (quantity × unit_price)');

            // Tax Information
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax rate percentage for this item');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount for this item');

            // Discount Information
            $table->decimal('discount_rate', 5, 2)->default(0)->comment('Discount rate percentage');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount for this item');

            // Ordering
            $table->integer('sort_order')->default(0)->comment('Order of items in the invoice');

            // Additional Information
            $table->json('metadata')->nullable()->comment('Additional metadata in JSON format');

            // Audit Fields
            $table->timestamps();

            // Indexes for better performance
            $table->index(['invoice_id', 'sort_order']);
            $table->index('service_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the invoice_items table.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
