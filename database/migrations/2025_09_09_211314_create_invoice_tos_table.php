<?php

/**
 * Migration for creating the invoice_tos table
 *
 * This table stores client/recipient information for invoices,
 * including company details, contact information, and payment details.
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
     * Creates the invoice_tos table with all necessary fields
     * for storing client information and payment details.
     */
    public function up(): void
    {
        Schema::create('invoice_tos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Reference to user who created/owns this client')->nullable();

            // Company Information
            $table->string('company_name')->comment('Client company name (e.g., REGENT AUCTIONEERS LTD)');
            $table->text('company_address')->nullable()->comment('Full company address including office location');

            // Contact Information
            $table->string('primary_phone')->nullable()->comment('Primary contact phone number');
            $table->string('secondary_phone')->nullable()->comment('Secondary contact phone number');
            $table->string('email')->nullable()->comment('Company email address');
            $table->string('website')->nullable()->comment('Company website URL');

            // Payment Details - MPESA
            $table->string('mpesa_account')->nullable()->comment('MPESA account number for payments');
            $table->string('mpesa_holder_name')->nullable()->comment('Name of MPESA account holder');

            // Payment Details - Bank Account
            $table->string('bank_name')->nullable()->comment('Bank name (e.g., KCB)');
            $table->string('bank_account')->nullable()->comment('Bank account number');
            $table->string('bank_holder_name')->nullable()->comment('Name of bank account holder');

            // Additional Information
            $table->text('additional_notes')->nullable()->comment('Any additional notes or special instructions');

            // Audit Fields
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for data retention');

            // Indexes for better performance
            $table->index('company_name');
            $table->index('email');
            $table->index(['user_id', 'company_name']); // Added composite index for user-specific client searches
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the invoice_tos table.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_tos');
    }
};
