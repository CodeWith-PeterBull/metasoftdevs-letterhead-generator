<?php

/**
 * Migration for creating the invoices table
 *
 * This table stores the main invoice records with totals, dates,
 * and references to company and client information.
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
     * Creates the invoices table with all necessary fields
     * for storing invoice header information and totals.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Reference to user who created/owns this invoice');

            // Invoice Identification
            $table->string('invoice_number')->unique()->comment('Unique invoice number (e.g., MSDI 2588)');
            $table->date('invoice_date')->comment('Date when invoice was created');
            $table->date('due_date')->nullable()->comment('Payment due date');

            // Company and Client References
            $table->foreignId('company_id')->constrained()->comment('Reference to issuing company');
            $table->foreignId('invoice_to_id')->constrained('invoice_tos')->comment('Reference to client/recipient');

            // Financial Information
            $table->decimal('sub_total', 15, 2)->default(0)->comment('Sum of all line items before taxes');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Total tax amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Total discount amount');
            $table->decimal('balance', 15, 2)->default(0)->comment('Outstanding balance amount');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Final total amount payable');

            // Currency
            $table->string('currency', 3)->default('KSH')->comment('Currency code (KSH, USD, EUR, etc.)');

            // Status and Notes
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])
                ->default('draft')
                ->comment('Current status of the invoice');

            $table->text('notes')->nullable()->comment('Additional notes or terms and conditions');
            $table->text('internal_notes')->nullable()->comment('Internal notes not visible to client');

            // Payment Information
            $table->datetime('paid_at')->nullable()->comment('Timestamp when invoice was marked as paid');
            $table->string('payment_method')->nullable()->comment('Method used for payment (MPESA, Bank Transfer, etc.)');
            $table->string('payment_reference')->nullable()->comment('Payment reference or transaction ID');

            // Audit Fields
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for data retention');

            // Indexes for better performance
            $table->index('invoice_number');
            $table->index('invoice_date');
            $table->index('status');
            $table->index(['company_id', 'invoice_date']);
            $table->index(['user_id', 'invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the invoices table.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
