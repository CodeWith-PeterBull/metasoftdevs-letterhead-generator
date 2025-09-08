<?php

/**
 * Company Model
 * 
 * Eloquent model for managing company information and letterhead data.
 * Handles comprehensive business details including contact information,
 * branding, and letterhead preferences for professional document generation.
 * 
 * This model provides a complete company profile system that integrates
 * with the letterhead generation workflow, allowing users to maintain
 * multiple company profiles and generate professional documents efficiently.
 * 
 * @package     MetaSoft Letterhead Generator
 * @category    Eloquent Model
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 * @version     1.0.0
 * @link        https://www.metasoftdevs.com
 * @since       File available since Release 1.0.0
 * 
 * @see         \App\Services\CompanyService Company business logic service
 * @see         \App\Http\Livewire\CompanyManagement Livewire CRUD component
 * 
 * @property int $id Primary key
 * @property int $user_id Foreign key to users table
 * @property string $name Company display name
 * @property string $address Full company address
 * @property string|null $phone_1 Primary phone number
 * @property string|null $phone_2 Secondary phone number
 * @property string|null $email_1 Primary email address
 * @property string|null $email_2 Secondary email address
 * @property string|null $website Website URL
 * @property string|null $linkedin_url LinkedIn profile URL
 * @property string|null $twitter_handle Twitter handle
 * @property string|null $facebook_url Facebook page URL
 * @property string|null $logo_path Logo file path
 * @property string $primary_color Primary brand color (hex)
 * @property string|null $industry Industry sector
 * @property string|null $description Company description
 * @property string $default_template Default letterhead template
 * @property string $default_paper_size Default paper size
 * @property bool $include_social_media Include social media in letterheads
 * @property bool $include_registration_details Include registration details
 * @property bool $is_active Company is active
 * @property bool $is_default Default company for user
 * @property \Carbon\Carbon|null $last_used_at Last used timestamp
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * 
 * @property-read \App\Models\User $user Company owner
 * @property-read string $full_address Formatted full address
 * @property-read string $display_name Best display name for company
 * @property-read array $contact_info Formatted contact information
 * @property-read bool $has_logo Whether company has a logo
 * @property-read string $logo_url Full URL to logo file
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Company extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone_1',
        'phone_2',
        'email_1',
        'email_2',
        'website',
        'linkedin_url',
        'twitter_handle',
        'facebook_url',
        'logo_path',
        'primary_color',
        'industry',
        'description',
        'default_template',
        'default_paper_size',
        'include_social_media',
        'include_registration_details',
        'is_active',
        'is_default',
        'last_used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'include_social_media' => 'boolean',
        'include_registration_details' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the company owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted full address.
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->address ?? ''
        );
    }

    /**
     * Get the best display name for the company.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->name
        );
    }

    /**
     * Get formatted contact information.
     */
    protected function contactInfo(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                return array_filter([
                    'phone_1' => $this->phone_1,
                    'phone_2' => $this->phone_2,
                    'email_1' => $this->email_1,
                    'email_2' => $this->email_2,
                    'website' => $this->website,
                    'linkedin_url' => $this->linkedin_url,
                    'twitter_handle' => $this->twitter_handle,
                    'facebook_url' => $this->facebook_url,
                ]);
            }
        );
    }

    /**
     * Check if company has a logo.
     */
    protected function hasLogo(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->getFirstMedia('logo') !== null
        );
    }

    /**
     * Get the full URL to the logo file.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $media = $this->getFirstMedia('logo');
                return $media ? $media->getUrl() : null;
            }
        );
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include companies for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get the default company for a user.
     */
    public function scopeDefault($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_default', true)
            ->where('is_active', true);
    }

    /**
     * Update the last used timestamp.
     */
    public function touch($attribute = null): bool
    {
        $this->last_used_at = now();
        return parent::touch($attribute);
    }

    /**
     * Set this company as the default for the user.
     */
    public function setAsDefault(): bool
    {
        // Remove default status from other companies
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this company as default
        $this->is_default = true;

        return $this->save();
    }

    /**
     * Get the logo URL for the company.
     */
    public function getLogoUrl(): ?string
    {
        if ($this->hasMedia('logo')) {
            return $this->getFirstMedia('logo')->getUrl();
        }

        return null;
    }

    /**
     * Get the company data formatted for letterhead generation.
     */
    public function toLetterheadData(): array
    {
        return [
            'company_name' => $this->display_name,
            'address' => $this->full_address,
            'phone' => $this->phone_1,
            'email' => $this->email_1,
            'website' => $this->website,
            'logo_url' => $this->logo_url,
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one default company per user
        static::saving(function ($company) {
            if ($company->is_default) {
                static::where('user_id', $company->user_id)
                    ->where('id', '!=', $company->id)
                    ->update(['is_default' => false]);
            }
        });

        // Update last_used_at when accessing company data
        static::retrieved(function ($company) {
            if (request()->is('letterhead*')) {
                $company->last_used_at = now();
                $company->saveQuietly(); // Save without triggering events
            }
        });
    }
}
