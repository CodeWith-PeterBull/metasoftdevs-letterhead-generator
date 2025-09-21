<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('document_path')->nullable()->comment('Path to generated PDF document');
            $table->datetime('document_generated_at')->nullable()->comment('Timestamp when PDF was last generated');
            $table->string('document_hash')->nullable()->comment('Hash of document content for cache invalidation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'document_generated_at', 'document_hash']);
        });
    }
};
