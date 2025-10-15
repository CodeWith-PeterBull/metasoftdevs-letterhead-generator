# Central Document Model Architecture

## Overview

This document outlines the comprehensive central document management system architecture for the MetaSoft Letterhead Generator application. The central Document model serves as a universal registry for all document types across various modules, providing unified document management, signature attachment, and audit trail capabilities.

**Document Version:** 1.0.0
**Created:** 2025-01-29
**Author:** Metasoft Developers
**Last Updated:** 2025-01-29

## Architecture Principles

### Core Concept: Document as Universal Registry
Every document across all modules (invoices, letters, contracts, reports) registers with a central Document model that provides:

- **Universal serial numbering** with format: `DOC-YYYY-XXXXXX`
- **Centralized storage management** organized by year/month/type structure
- **Unified signature attachment** through polymorphic relationships
- **Cross-module document relationships** enabling document linking
- **Complete audit trail** with version management and integrity verification
- **Company-scoped documents** with nullable company relationships

### Sustainability Benefits
- Single source of truth for all documents
- Consistent patterns across modules
- Easy addition of new document types
- Shared signature functionality
- Centralized storage management

## Database Schema Design

### Primary Document Table

```sql
-- documents table
CREATE TABLE documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Document Identity
    serial_number VARCHAR(20) UNIQUE NOT NULL,           -- DOC-2025-000001
    document_type VARCHAR(50) NOT NULL,                  -- invoice, letter, contract, report
    title VARCHAR(255) NOT NULL,                         -- Human-readable title

    -- Storage Management
    storage_path TEXT NOT NULL,                          -- Actual file location
    file_size BIGINT UNSIGNED DEFAULT 0,                -- File size in bytes
    mime_type VARCHAR(100) DEFAULT 'application/pdf',   -- File MIME type
    checksum VARCHAR(64) NULL,                           -- File integrity verification (SHA-256)

    -- Status & Lifecycle
    status ENUM('draft', 'generated', 'sent', 'archived', 'deleted') DEFAULT 'draft',
    generated_at TIMESTAMP NULL,                         -- When PDF was created
    version INTEGER UNSIGNED DEFAULT 1,                 -- Document version number

    -- Polymorphic Source Relationship
    documentable_type VARCHAR(255) NOT NULL,            -- App\Models\Invoice, etc.
    documentable_id BIGINT UNSIGNED NOT NULL,           -- ID of source document

    -- Relationships
    user_id BIGINT UNSIGNED NOT NULL,                   -- Document owner
    company_id BIGINT UNSIGNED NULL,                    -- Associated company (nullable)

    -- Metadata
    metadata JSON NULL,                                  -- Type-specific data storage

    -- Standard Laravel timestamps
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_documents_serial (serial_number),
    INDEX idx_documents_type (document_type),
    INDEX idx_documents_user (user_id),
    INDEX idx_documents_company (company_id),
    INDEX idx_documents_status (status),
    INDEX idx_documents_polymorphic (documentable_type, documentable_id),

    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);
```

### Signature Attachment Table

```sql
-- document_signature_attachments table
CREATE TABLE document_signature_attachments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Core Relationships
    document_id BIGINT UNSIGNED NOT NULL,               -- References documents table
    signature_id BIGINT UNSIGNED NOT NULL,             -- References document_signatures table

    -- Positioning & Metadata
    position INTEGER UNSIGNED DEFAULT 1,                -- Signature order on document
    signed_at TIMESTAMP NULL,                           -- When signature was applied

    -- Standard Laravel timestamps
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY uk_document_signature (document_id, signature_id),

    -- Foreign key constraints
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signature_id) REFERENCES document_signatures(id) ON DELETE CASCADE,

    -- Indexes
    INDEX idx_attachments_document (document_id),
    INDEX idx_attachments_signature (signature_id),
    INDEX idx_attachments_position (position)
);
```

## Model Architecture

### Central Document Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Document Model
 *
 * Central registry for all document types across the application.
 * Provides unified document management with signature support.
 *
 * @property int $id
 * @property string $serial_number Auto-generated document serial
 * @property string $document_type Type of document (invoice, letter, etc.)
 * @property string $title Human-readable document title
 * @property string $storage_path File storage location
 * @property int $file_size File size in bytes
 * @property string $mime_type File MIME type
 * @property string|null $checksum File integrity checksum
 * @property string $status Document lifecycle status
 * @property Carbon|null $generated_at When document was generated
 * @property int $version Document version number
 * @property string $documentable_type Polymorphic source type
 * @property int $documentable_id Polymorphic source ID
 * @property int $user_id Document owner
 * @property int|null $company_id Associated company (nullable)
 * @property array|null $metadata Type-specific metadata
 */
class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'serial_number',
        'document_type',
        'title',
        'storage_path',
        'file_size',
        'mime_type',
        'checksum',
        'status',
        'generated_at',
        'version',
        'documentable_type',
        'documentable_id',
        'user_id',
        'company_id',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'generated_at' => 'datetime',
        'metadata' => 'array',
        'file_size' => 'integer',
        'version' => 'integer'
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Document $document) {
            if (empty($document->serial_number)) {
                $document->serial_number = static::generateSerialNumber();
            }
        });
    }

    /**
     * Get the owning documentable model.
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company associated with the document.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the signatures attached to this document.
     */
    public function signatures(): BelongsToMany
    {
        return $this->belongsToMany(DocumentSignature::class, 'document_signature_attachments')
                    ->withPivot('position', 'signed_at')
                    ->withTimestamps()
                    ->orderByPivot('position');
    }

    /**
     * Generate unique serial number for new documents.
     */
    public static function generateSerialNumber(): string
    {
        $year = now()->year;
        $lastSerial = static::whereYear('created_at', $year)
                           ->where('serial_number', 'LIKE', "DOC-{$year}-%")
                           ->max('serial_number');

        $nextNumber = $lastSerial ?
            (int) substr($lastSerial, -6) + 1 : 1;

        return "DOC-{$year}-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get storage path for document type.
     */
    public static function getStoragePath(string $documentType, Carbon $date = null): string
    {
        $date = $date ?? now();
        return "documents/{$date->year}/{$date->format('m')}/{$documentType}";
    }

    /**
     * Get full file URL.
     */
    public function getFileUrl(): string
    {
        return Storage::disk('public')->url($this->storage_path);
    }

    /**
     * Verify file integrity.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->checksum || !Storage::disk('public')->exists($this->storage_path)) {
            return false;
        }

        $fileChecksum = hash_file('sha256', Storage::disk('public')->path($this->storage_path));
        return hash_equals($this->checksum, $fileChecksum);
    }

    /**
     * Attach signatures to document.
     */
    public function attachSignatures(array $signatureIds): void
    {
        $attachData = [];
        foreach ($signatureIds as $index => $signatureId) {
            $attachData[$signatureId] = [
                'position' => $index + 1,
                'signed_at' => now()
            ];
        }

        $this->signatures()->syncWithoutDetaching($attachData);

        // Track signature usage
        DocumentSignature::whereIn('id', $signatureIds)->each(function ($signature) {
            $signature->incrementUsage();
        });
    }

    /**
     * Scope: Filter by document type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
```

## Storage Architecture

### Directory Structure

```
storage/app/public/documents/
├── 2025/                           # Year-based organization
│   ├── 01/                         # Month-based sub-organization
│   │   ├── invoices/
│   │   │   ├── DOC-2025-000001.pdf
│   │   │   └── DOC-2025-000015.pdf
│   │   ├── letters/
│   │   │   └── DOC-2025-000002.pdf
│   │   └── contracts/
│   │       └── DOC-2025-000003.pdf
│   └── 02/
│       ├── invoices/
│       └── letters/
├── 2024/                           # Previous years maintained
└── temp/                           # Temporary generation files
```

### Storage Service

```php
<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Document Storage Service
 *
 * Manages centralized document storage, organization, and file operations.
 * Provides consistent storage patterns across all document types.
 */
class DocumentStorageService
{
    /**
     * Get organized storage path for document type.
     */
    public static function getStoragePath(string $documentType, Carbon $date = null): string
    {
        $date = $date ?? now();
        return "documents/{$date->year}/{$date->format('m')}/{$documentType}";
    }

    /**
     * Generate unique filename for document.
     */
    public static function generateFilename(string $serialNumber, string $extension = 'pdf'): string
    {
        return "{$serialNumber}.{$extension}";
    }

    /**
     * Store document file with proper organization.
     */
    public static function storeDocument(string $content, string $documentType, string $serialNumber): string
    {
        $storagePath = static::getStoragePath($documentType);
        $filename = static::generateFilename($serialNumber);
        $fullPath = "{$storagePath}/{$filename}";

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($storagePath);

        // Store file
        Storage::disk('public')->put($fullPath, $content);

        return $fullPath;
    }

    /**
     * Calculate file checksum for integrity verification.
     */
    public static function calculateChecksum(string $filePath): string
    {
        return hash_file('sha256', Storage::disk('public')->path($filePath));
    }

    /**
     * Clean up temporary files older than specified days.
     */
    public static function cleanupTempFiles(int $days = 1): void
    {
        $tempPath = 'documents/temp';
        $files = Storage::disk('public')->files($tempPath);

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            if ($lastModified < now()->subDays($days)->timestamp) {
                Storage::disk('public')->delete($file);
            }
        }
    }
}
```

## Integration Patterns

### Module Integration Example (Invoice)

```php
<?php

namespace App\Models;

use App\Models\Document;
use App\Services\DocumentStorageService;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Invoice Model Enhancement
 *
 * Enhanced to integrate with central Document model for unified management.
 */
class Invoice extends Model
{
    // ... existing invoice logic ...

    /**
     * Get the associated document.
     */
    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    /**
     * Generate and register document.
     *
     * Replaces individual PDF generation methods with centralized approach.
     */
    public function generateDocument(): Document
    {
        // Generate PDF content (existing logic)
        $pdfContent = $this->generatePdfContent();

        // Store document with centralized service
        $storagePath = DocumentStorageService::storeDocument(
            $pdfContent,
            'invoice',
            Document::generateSerialNumber()
        );

        // Calculate file checksum for integrity
        $checksum = DocumentStorageService::calculateChecksum($storagePath);

        // Register with Document model
        return $this->document()->create([
            'document_type' => 'invoice',
            'title' => "Invoice {$this->invoice_number}",
            'storage_path' => $storagePath,
            'file_size' => strlen($pdfContent),
            'mime_type' => 'application/pdf',
            'checksum' => $checksum,
            'status' => 'generated',
            'generated_at' => now(),
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'metadata' => [
                'invoice_number' => $this->invoice_number,
                'amount' => $this->grand_total,
                'currency' => $this->currency,
                'client_company' => $this->invoiceTo->company_name ?? null
            ]
        ]);
    }

    /**
     * Get or generate document.
     */
    public function getOrGenerateDocument(): Document
    {
        return $this->document ?? $this->generateDocument();
    }

    /**
     * Check if invoice has generated document.
     */
    public function hasDocument(): bool
    {
        return $this->document !== null;
    }
}
```

## Signature Integration Strategy

### Signature Attachment Process

```php
// Universal signature attachment to any document
$document = $invoice->getOrGenerateDocument();

// Attach multiple signatures with positioning
$signatureIds = [1, 2, 3]; // Selected signature IDs
$document->attachSignatures($signatureIds);

// Signatures are now available via relationship
$signatures = $document->signatures; // Ordered by position
```

### PDF Template Integration

```blade
{{-- Universal signature rendering for any document PDF template --}}
@if($document->signatures->isNotEmpty())
    <div class="document-signatures-section" style="margin-top: 2rem;">
        @foreach($document->signatures as $signature)
            <div class="signature-position-{{ $signature->pivot->position }}" style="margin-bottom: 1rem;">
                <x-signature-renderer
                    :signature="$signature"
                    :document-context="true"
                    size="medium" />

                @if($signature->pivot->signed_at)
                    <div style="font-size: 10px; color: #666; margin-top: 0.5rem;">
                        Signed: {{ $signature->pivot->signed_at->format('Y-m-d H:i:s') }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
```

## Migration Strategy

### Phase 1: Foundation
1. Create documents table migration
2. Create document_signature_attachments table migration
3. Create Document model with relationships
4. Create DocumentStorageService

### Phase 2: Module Integration
1. Add document relationship to existing models (Invoice, etc.)
2. Update PDF generation to use Document model
3. Migrate existing PDFs to new structure (optional)
4. Update signature attachment to use Document

### Phase 3: Enhancement
1. Update signature components to work with Document
2. Enhance PDF templates with signature rendering
3. Implement usage tracking and analytics
4. Add document versioning and audit trail

## Security Considerations

- **File Integrity**: SHA-256 checksums verify document authenticity
- **Access Control**: User-scoped documents with company-level isolation
- **Audit Trail**: Complete lifecycle tracking with timestamps
- **Storage Security**: Organized storage with proper permissions
- **Input Validation**: Comprehensive validation for all document operations

## Performance Optimizations

- **Database Indexing**: Strategic indexes for common query patterns
- **Eager Loading**: Optimize signature and relationship queries
- **File Caching**: Browser-cacheable file URLs with proper headers
- **Storage Optimization**: Year/month organization prevents directory bloat
- **Background Processing**: Queue document generation for large files

## Monitoring and Maintenance

- **Storage Monitoring**: Track disk usage and implement cleanup policies
- **Integrity Checks**: Periodic verification of file checksums
- **Performance Metrics**: Monitor document generation and retrieval times
- **Error Tracking**: Log document operations for debugging
- **Backup Strategy**: Ensure document files are included in backup procedures

## Future Extensibility

- **Document Versioning**: Support for document revisions and history
- **Digital Signatures**: Integration with PKI for legal digital signatures
- **Document Templates**: Template system for consistent document generation
- **Collaboration Features**: Multi-user document editing and approval workflows
- **API Integration**: RESTful API for external document management systems

---

*This architecture provides a sustainable, scalable foundation for document management across all application modules while maintaining simplicity and consistency.*