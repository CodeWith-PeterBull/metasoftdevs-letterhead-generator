<?php

/**
 * Companies Table Migration
 * 
 * Creates the companies table to store comprehensive company information
 * for letterhead generation. Includes all necessary fields for complete
 * business identity and contact details.
 * 
 * @package     MetaSoft Letterhead Generator
 * @category    Database Migration
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 * @version     1.0.0
 * @link        https://www.metasoftdevs.com
 * @since       File available since Release 1.0.0
 * 
 * @see         \App\Models\Company Company model
 * @see         \App\Services\CompanyService Company business logic service
 */

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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // User association
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic company information
            $table->string('name')->index();

            // Address information - handles full address as single field
            $table->text('address');

            // Contact information
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('email_1')->nullable();
            $table->string('email_2')->nullable();
            $table->string('website')->nullable();

            // Social media and online presence
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->string('facebook_url')->nullable();

            // Branding and visual identity (logo handled by Spatie Media Library)
            $table->string('logo_path')->nullable(); // Logo file path
            $table->string('primary_color', 7)->default('#000000'); // Hex color

            // Business information
            $table->string('industry')->nullable();
            $table->text('description')->nullable();

            // Letterhead preferences
            $table->enum('default_template', ['classic', 'modern_green', 'corporate_blue', 'elegant_gray'])->default('classic');
            $table->enum('default_paper_size', ['us_letter', 'a4', 'legal'])->default('us_letter');
            $table->boolean('include_social_media')->default(false);
            $table->boolean('include_registration_details')->default(false);

            // Status and management
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_default']);
            $table->index(['name', 'is_active']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
