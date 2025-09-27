<?php

/**
 * DocumentSignature Model
 *
 * Represents digital signatures for documents within the MetaSoft LetterHead system.
 * Supports multiple signature components (name, title, signature image, stamp),
 * flexible storage options (file/base64), and comprehensive display settings.
 *
 * @author MetaSoft Team
 *
 * @version 1.0
 *
 * @since Laravel 12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentSignature extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'signature_name',
        'description',
        'is_default',
        'is_active',
        'full_name',
        'position_title',
        'initials',
        'signature_image_type',
        'signature_image_data',
        'signature_image_width',
        'signature_image_height',
        'stamp_image_type',
        'stamp_image_data',
        'stamp_image_width',
        'stamp_image_height',
        'display_name',
        'display_title',
        'display_date',
        'date_format',
        'font_family',
        'font_size',
        'text_color',
        'default_position',
        'default_width',
        'default_height',
        'include_border',
        'border_color',
        'background_color',
        'usage_count',
        'last_used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'display_name' => 'boolean',
        'display_title' => 'boolean',
        'display_date' => 'boolean',
        'include_border' => 'boolean',
        'signature_image_width' => 'integer',
        'signature_image_height' => 'integer',
        'stamp_image_width' => 'integer',
        'stamp_image_height' => 'integer',
        'default_width' => 'integer',
        'default_height' => 'integer',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'signature_image_data',
        'stamp_image_data',
    ];

    /**
     * Get the user who owns this signature.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the signature image URL for file-based storage.
     */
    public function getSignatureImageUrlAttribute(): ?string
    {
        if ($this->signature_image_type === 'file' && $this->signature_image_data) {
            return Storage::disk('public')->url($this->signature_image_data);
        }

        return null;
    }

    /**
     * Get the signature image as base64 data URI.
     */
    public function getSignatureImageBase64Attribute(): ?string
    {
        if ($this->signature_image_type === 'base64' && $this->signature_image_data) {
            // Assume data already includes data URI prefix
            return $this->signature_image_data;
        }

        if ($this->signature_image_type === 'file' && $this->signature_image_data) {
            try {
                $filePath = Storage::disk('public')->path($this->signature_image_data);
                if (file_exists($filePath)) {
                    $imageData = file_get_contents($filePath);
                    $mimeType = mime_content_type($filePath);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Exception $e) {
                // Log error and return null
                Log::warning('Failed to convert signature image to base64', [
                    'signature_id' => $this->id,
                    'file_path' => $this->signature_image_data,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Get the stamp image URL for file-based storage.
     */
    public function getStampImageUrlAttribute(): ?string
    {
        if ($this->stamp_image_type === 'file' && $this->stamp_image_data) {
            return Storage::disk('public')->url($this->stamp_image_data);
        }

        return null;
    }

    /**
     * Get the stamp image as base64 data URI.
     */
    public function getStampImageBase64Attribute(): ?string
    {
        if ($this->stamp_image_type === 'base64' && $this->stamp_image_data) {
            return $this->stamp_image_data;
        }

        if ($this->stamp_image_type === 'file' && $this->stamp_image_data) {
            try {
                $filePath = Storage::disk('public')->path($this->stamp_image_data);
                if (file_exists($filePath)) {
                    $imageData = file_get_contents($filePath);
                    $mimeType = mime_content_type($filePath);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to convert stamp image to base64', [
                    'signature_id' => $this->id,
                    'file_path' => $this->stamp_image_data,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Get formatted font size in pixels.
     */
    public function getFontSizePixelsAttribute(): int
    {
        return match ($this->font_size) {
            'small' => 12,
            'medium' => 14,
            'large' => 16,
            default => 14
        };
    }

    /**
     * Check if signature has a signature image.
     */
    public function hasSignatureImage(): bool
    {
        return ! empty($this->signature_image_data);
    }

    /**
     * Check if signature has a stamp image.
     */
    public function hasStampImage(): bool
    {
        return ! empty($this->stamp_image_data);
    }

    /**
     * Record usage of this signature.
     */
    public function recordUsage(?string $documentType = null, ?int $documentId = null): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);

        // Log usage for analytics if needed
        Log::info('Signature used', [
            'signature_id' => $this->id,
            'user_id' => $this->user_id,
            'document_type' => $documentType,
            'document_id' => $documentId,
        ]);
    }

    /**
     * Generate HTML for document embedding.
     */
    public function renderForDocument(array $options = []): string
    {
        $width = $options['width'] ?? $this->default_width;
        $height = $options['height'] ?? $this->default_height;
        $position = $options['position'] ?? $this->default_position;
        $includeDate = $options['include_date'] ?? $this->display_date;
        $dateFormat = $options['date_format'] ?? $this->date_format;

        $html = '<div class="document-signature" style="';
        $html .= "width: {$width}px; ";
        $html .= "height: {$height}px; ";
        $html .= 'position: relative; ';
        $html .= "text-align: {$position}; ";

        if ($this->background_color) {
            $html .= "background-color: {$this->background_color}; ";
        }

        if ($this->include_border) {
            $html .= "border: 1px solid {$this->border_color}; ";
        }

        $html .= '">';

        // Name
        if ($this->display_name && $this->full_name) {
            $html .= '<div class="signature-name" style="';
            $html .= "font-family: {$this->font_family}; ";
            $html .= "font-size: {$this->font_size_pixels}px; ";
            $html .= "color: {$this->text_color}; ";
            $html .= 'font-weight: bold; margin-bottom: 2px;">';
            $html .= htmlspecialchars($this->full_name);

            // Add initials if available
            if (!empty($this->initials)) {
                $html .= ' <span style="color: #666; font-size: '.($this->font_size_pixels - 2).'px; font-weight: normal;">('.htmlspecialchars($this->initials).')</span>';
            }

            $html .= '</div>';
        }

        // Title
        if ($this->display_title && $this->position_title) {
            $html .= '<div class="signature-title" style="';
            $html .= "font-family: {$this->font_family}; ";
            $html .= 'font-size: ' . ($this->font_size_pixels - 2) . 'px; ';
            $html .= "color: {$this->text_color}; ";
            $html .= 'margin-bottom: 4px;">';
            $html .= htmlspecialchars($this->position_title);
            $html .= '</div>';
        }

        // Signature Image
        if ($this->hasSignatureImage()) {
            $html .= '<div class="signature-image" style="margin: 4px 0;">';
            $html .= '<img src="' . $this->signature_image_base64 . '" ';
            $html .= 'alt="Signature" style="max-width: 100%; height: auto; max-height: 60px;">';
            $html .= '</div>';
        }

        // Stamp and Date row
        if ($this->hasStampImage() || $includeDate) {
            $html .= '<div class="signature-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">';

            if ($this->hasStampImage()) {
                $html .= '<div class="signature-stamp">';
                $html .= '<img src="' . $this->stamp_image_base64 . '" ';
                $html .= 'alt="Stamp" style="width: 40px; height: 40px; object-fit: contain;">';
                $html .= '</div>';
            }

            if ($includeDate) {
                $html .= '<div class="signature-date" style="';
                $html .= "font-family: {$this->font_family}; ";
                $html .= 'font-size: ' . ($this->font_size_pixels - 2) . 'px; ';
                $html .= "color: {$this->text_color}; ";
                $html .= '">';
                $html .= now()->format($dateFormat);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Scope to filter signatures by authenticated user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get only active signatures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default signature for user.
     */
    public function scopeDefault($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_default', true)
            ->where('is_active', true);
    }

    /**
     * Scope to order by usage (most used first).
     */
    public function scopeOrderByUsage($query)
    {
        return $query->orderBy('usage_count', 'desc')
            ->orderBy('last_used_at', 'desc');
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Ensure only one default signature per user
        static::saving(function ($signature) {
            if ($signature->is_default) {
                // Remove default from all other signatures for this user
                static::where('user_id', $signature->user_id)
                    ->where('id', '!=', $signature->id)
                    ->update(['is_default' => false]);
            }
        });

        // Ensure user always has at least one default signature
        static::created(function ($signature) {
            // If this is the first signature for the user, make it default
            $userSignatureCount = static::where('user_id', $signature->user_id)->count();
            if ($userSignatureCount === 1) {
                $signature->update(['is_default' => true]);
            }
        });

        // Handle signature deletion
        static::deleting(function ($signature) {
            // If deleting the default signature, find another one to make default
            if ($signature->is_default) {
                $newDefault = static::where('user_id', $signature->user_id)
                    ->where('id', '!=', $signature->id)
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            // Clean up files when signature is deleted
            if ($signature->signature_image_type === 'file' && $signature->signature_image_data) {
                Storage::disk('public')->delete($signature->signature_image_data);
            }
            if ($signature->stamp_image_type === 'file' && $signature->stamp_image_data) {
                Storage::disk('public')->delete($signature->stamp_image_data);
            }
        });
    }
}
