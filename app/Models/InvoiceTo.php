<?php

/**
 * InvoiceTo Model
 *
 * Represents client/recipient information for invoices.
 * Stores company details, contact information, and payment details
 * for entities that receive invoices.
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

class InvoiceTo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_tos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Added user_id to fillable attributes
        'company_name',
        'company_address',
        'primary_phone',
        'secondary_phone',
        'email',
        'website',
        'mpesa_account',
        'mpesa_holder_name',
        'bank_name',
        'bank_account',
        'bank_holder_name',
        'additional_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who owns this client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoices for this client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the full contact information formatted for display.
     */
    public function getFullContactAttribute(): string
    {
        $contact = [];

        if ($this->primary_phone) {
            $contact[] = $this->primary_phone;
        }

        if ($this->secondary_phone) {
            $contact[] = $this->secondary_phone;
        }

        if ($this->email) {
            $contact[] = $this->email;
        }

        return implode(' | ', $contact);
    }

    /**
     * Get formatted payment details for MPESA.
     */
    public function getMpesaDetailsAttribute(): ?string
    {
        if (! $this->mpesa_account) {
            return null;
        }

        return "MPESA Account: {$this->mpesa_account}".
            ($this->mpesa_holder_name ? "\nHolder Name: {$this->mpesa_holder_name}" : '');
    }

    /**
     * Get formatted payment details for bank account.
     */
    public function getBankDetailsAttribute(): ?string
    {
        if (! $this->bank_account) {
            return null;
        }

        $details = ($this->bank_name ? "{$this->bank_name} " : '')."Account: {$this->bank_account}";

        if ($this->bank_holder_name) {
            $details .= "\nHolder Name: {$this->bank_holder_name}";
        }

        return $details;
    }

    /**
     * Scope to filter clients by authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to search clients by company name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCompanyName($query, string $name)
    {
        return $query->where('company_name', 'like', "%{$name}%");
    }
}
