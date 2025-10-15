<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds paid_amount column to track partial or full payments made against invoices.
     * This enables proper tracking of payment history and accurate balance calculations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('balance')->comment('Total amount paid against this invoice (for partial payments)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the paid_amount column from the invoices table.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
