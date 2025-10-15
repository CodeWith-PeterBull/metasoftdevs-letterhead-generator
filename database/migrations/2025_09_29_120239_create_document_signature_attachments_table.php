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
        Schema::create('document_signature_attachments', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('signature_id')->constrained('document_signatures')->onDelete('cascade');

            // Positioning and metadata
            $table->unsignedInteger('position')->default(1);
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();

            // Ensure unique signature per document
            $table->unique(['document_id', 'signature_id'], 'uk_document_signature');

            // Performance indexes
            $table->index('document_id');
            $table->index('signature_id');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signature_attachments');
    }
};
