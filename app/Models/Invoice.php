<?php

/**
 * Invoice Model
 *
 * Represents the main invoice entity with financial totals,
 * status tracking, and relationships to company and client information.
 *
 * @author Your Name
 *
 * @version 1.0
 *
 * @since Laravel 12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Added user_id to fillable attributes
        'invoice_number',
        'invoice_date',
        'due_date',
        'company_id',
        'invoice_to_id',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'balance',
        'grand_total',
        'currency',
        'status',
        'notes',
        'internal_notes',
        'paid_at',
        'payment_method',
        'payment_reference',
        'document_path',
        'document_generated_at',
        'document_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sub_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_at' => 'datetime',
        'document_generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who owns this invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that issued this invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the client/recipient of this invoice.
     */
    public function invoiceTo(): BelongsTo
    {
        return $this->belongsTo(InvoiceTo::class);
    }

    /**
     * Get all items for this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Calculate and update invoice totals based on items.
     */
    public function calculateTotals(): void
    {
        $items = $this->items;

        $this->sub_total = $items->sum('line_total');
        $this->tax_amount = $items->sum('tax_amount');
        $this->discount_amount = $items->sum('discount_amount');

        // Calculate grand total: sub_total + tax - discount
        $this->grand_total = $this->sub_total + $this->tax_amount - $this->discount_amount;

        // Balance is typically the same as grand total for new invoices
        if ($this->balance == 0) {
            $this->balance = $this->grand_total;
        }

        $this->save();
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'balance' => 0,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Get formatted invoice number with prefix.
     */
    public function getFormattedNumberAttribute(): string
    {
        return "Number: {$this->invoice_number}";
    }

    /**
     * Get formatted currency amount.
     */
    public function formatCurrency(float $amount): string
    {
        return "{$this->currency} ".number_format($amount, 2);
    }

    /**
     * Check if invoice is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            $this->status !== 'paid' &&
            $this->balance > 0;
    }

    /**
     * Scope to filter invoices by authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter invoices by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->where('balance', '>', 0);
    }

    /**
     * Generate PDF for this invoice.
     */
    public function generatePdf(): string
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.template', ['invoice' => $this->load(['company', 'invoiceTo', 'items'])]);
        $pdf->setPaper('A4', 'portrait');

        $filename = "invoice_{$this->invoice_number}_{$this->id}.pdf";
        $path = "invoices/{$this->user_id}/{$filename}";

        $pdf->save(storage_path("app/public/{$path}"));

        $this->update([
            'document_path' => $path,
            'document_generated_at' => now(),
            'document_hash' => $this->generateDocumentHash(),
        ]);

        return $path;
    }

    /**
     * Check if PDF needs regeneration (invoice has been updated since last generation).
     */
    public function needsPdfRegeneration(): bool
    {
        if (! $this->document_generated_at || ! $this->document_hash) {
            return true;
        }

        return $this->document_hash !== $this->generateDocumentHash();
    }

    /**
     * Generate hash of invoice content for cache invalidation.
     */
    public function generateDocumentHash(): string
    {
        $baseContent = [
            $this->invoice_number,
            $this->invoice_date->format('Y-m-d'),
            $this->due_date?->format('Y-m-d'),
            $this->sub_total,
            $this->tax_amount,
            $this->discount_amount,
            $this->grand_total,
            $this->status,
            $this->notes ?? '',
            $this->updated_at->format('Y-m-d H:i:s'),
        ];

        $itemsContent = $this->items->map(function ($item) {
            return $item->service_name.'|'.$item->description.'|'.$item->quantity.'|'.$item->unit_price;
        })->toArray();

        $content = collect($baseContent)->merge($itemsContent)->join('|');

        return md5($content);
    }

    /**
     * Get the URL for the generated PDF document.
     */
    public function getPdfUrl(): ?string
    {
        if (! $this->document_path) {
            return null;
        }

        return asset("storage/{$this->document_path}");
    }

    /**
     * Check if PDF document exists.
     */
    public function hasPdf(): bool
    {
        return $this->document_path && file_exists(storage_path("app/public/{$this->document_path}"));
    }

    /**
     * Get or generate PDF for this invoice.
     */
    public function getOrGeneratePdf(): string
    {
        if (! $this->hasPdf() || $this->needsPdfRegeneration()) {
            return $this->generatePdf();
        }

        return $this->document_path;
    }
}
