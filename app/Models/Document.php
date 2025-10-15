<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * Document Model
 *
 * Central registry for all document types across the application.
 * Provides unified document management with signature support.
 */
class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

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
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'metadata' => 'array',
            'file_size' => 'integer',
            'version' => 'integer',
        ];
    }

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

        return "DOC-{$year}-".str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get storage path for document type.
     */
    public static function getStoragePath(string $documentType, ?Carbon $date = null): string
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
        if (! $this->checksum || ! Storage::disk('public')->exists($this->storage_path)) {
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
                'signed_at' => now(),
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
