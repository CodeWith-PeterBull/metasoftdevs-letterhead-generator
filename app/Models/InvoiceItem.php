<?php

/**
 * InvoiceItem Model
 *
 * Represents individual line items within an invoice.
 * Stores service details, quantities, pricing, and calculations
 * for each item on an invoice.
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

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'service_name',
        'description',
        'period',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'sort_order',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sort_order' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the invoice that owns this item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate line total based on quantity and unit price.
     */
    public function calculateLineTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Apply discount
        $discountAmount = ($subtotal * $this->discount_rate) / 100;
        $afterDiscount = $subtotal - $discountAmount;

        // Apply tax
        $taxAmount = ($afterDiscount * $this->tax_rate) / 100;

        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->line_total = $afterDiscount + $taxAmount;
    }

    /**
     * Get formatted service name with period.
     */
    public function getFullServiceNameAttribute(): string
    {
        $name = $this->service_name;

        if ($this->period) {
            $name .= " ({$this->period})";
        }

        return $name;
    }

    /**
     * Get formatted unit price with currency.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        $currency = $this->invoice->currency ?? 'KSH';

        return "{$currency} ".number_format($this->unit_price, 2);
    }

    /**
     * Get formatted line total with currency.
     */
    public function getFormattedLineTotalAttribute(): string
    {
        $currency = $this->invoice->currency ?? 'KSH';

        return "{$currency} ".number_format($this->line_total, 2);
    }

    /**
     * Boot method to handle model events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate totals when saving
        static::saving(function ($item) {
            $item->calculateLineTotal();
        });

        // Update invoice totals when item is saved or deleted
        static::saved(function ($item) {
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotals();
        });
    }

    /**
     * Scope to order items by sort order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
