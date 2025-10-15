<?php

/**
 * Create Documents Table Migration
 *
 * Creates the central documents table for unified document management across
 * all application modules. This table serves as a registry for all document
 * types (invoices, letters, contracts, reports) with unified storage,
 * signature attachment, and audit trail capabilities.
 *
 * Table Structure:
 * - Document identity with universal serial numbering (DOC-YYYY-XXXXXX)
 * - Storage management with organized file paths and integrity verification
 * - Polymorphic relationships to source document models
 * - Company-scoped documents with nullable company relationships
 * - Status lifecycle management and audit trail
 * - Metadata storage for type-specific document information
 *
 * Relationships:
 * - belongsTo: User (document owner)
 * - belongsTo: Company (nullable - for company-scoped documents)
 * - morphTo: Documentable (polymorphic - source document like Invoice)
 * - belongsToMany: DocumentSignature (via document_signature_attachments)
 *
 * @category    Database Migration
 *
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 *
 * @version     1.0.0
 *
 * @link        https://www.metasoftdevs.com
 * @since       Migration available since Release 1.0.0
 * @see         \App\Models\Document Central document model
 * @see         \App\Services\DocumentStorageService Document storage management
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the documents table with comprehensive field structure for
     * unified document management across all application modules.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Document Identity and Classification
            $table->string('serial_number', 20)->unique()->comment('Auto-generated universal document serial (DOC-YYYY-XXXXXX)');
            $table->string('document_type', 50)->comment('Type of document: invoice, letter, contract, report, etc.');
            $table->string('title')->comment('Human-readable document title for identification');

            // Storage and File Management
            $table->text('storage_path')->comment('Relative path to stored document file');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size in bytes for storage monitoring');
            $table->string('mime_type', 100)->default('application/pdf')->comment('File MIME type for proper handling');
            $table->string('checksum', 64)->nullable()->comment('SHA-256 file checksum for integrity verification');

            // Document Status and Lifecycle
            $table->enum('status', ['draft', 'generated', 'sent', 'archived', 'deleted'])
                ->default('draft')
                ->comment('Document lifecycle status for workflow management');
            $table->timestamp('generated_at')->nullable()->comment('When document file was successfully generated');
            $table->unsignedInteger('version')->default(1)->comment('Document version number for revision tracking');

            // Polymorphic Source Relationship
            $table->string('documentable_type')->comment('Source model class name (App\\Models\\Invoice, etc.)');
            $table->unsignedBigInteger('documentable_id')->comment('Source model ID for polymorphic relationship');

            // Ownership and Company Association
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Document owner - cascades delete when user is removed');

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('Associated company (nullable for personal documents)');

            // Metadata Storage
            $table->json('metadata')->nullable()->comment('Type-specific document metadata in JSON format');

            // Standard Laravel Timestamps
            $table->timestamps();

            // Performance Indexes
            $table->index('serial_number', 'idx_documents_serial');
            $table->index('document_type', 'idx_documents_type');
            $table->index('user_id', 'idx_documents_user');
            $table->index('company_id', 'idx_documents_company');
            $table->index('status', 'idx_documents_status');
            $table->index(['documentable_type', 'documentable_id'], 'idx_documents_polymorphic');
            $table->index('created_at', 'idx_documents_created');

            // Composite indexes for common query patterns
            $table->index(['user_id', 'document_type'], 'idx_documents_user_type');
            $table->index(['company_id', 'document_type'], 'idx_documents_company_type');
            $table->index(['user_id', 'status'], 'idx_documents_user_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the documents table and all associated indexes.
     * WARNING: This will permanently delete all document registry data.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
