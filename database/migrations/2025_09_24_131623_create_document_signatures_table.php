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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic Information
            $table->string('signature_name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Signature Components
            $table->string('full_name');
            $table->string('position_title')->nullable();
            $table->string('initials', 10)->nullable();

            // Image Storage (Base64 or File Path)
            $table->enum('signature_image_type', ['base64', 'file'])->default('file');
            $table->text('signature_image_data')->nullable();
            $table->integer('signature_image_width')->nullable();
            $table->integer('signature_image_height')->nullable();

            // Stamp/Seal (Optional)
            $table->enum('stamp_image_type', ['base64', 'file'])->nullable();
            $table->text('stamp_image_data')->nullable();
            $table->integer('stamp_image_width')->nullable();
            $table->integer('stamp_image_height')->nullable();

            // Display Settings
            $table->boolean('display_name')->default(true);
            $table->boolean('display_title')->default(true);
            $table->boolean('display_date')->default(true);
            $table->string('date_format', 50)->default('d/m/Y');

            // Styling Options
            $table->string('font_family', 100)->default('Arial');
            $table->enum('font_size', ['small', 'medium', 'large'])->default('medium');
            $table->string('text_color', 7)->default('#000000');

            // Positioning
            $table->enum('default_position', ['left', 'center', 'right'])->default('right');
            $table->integer('default_width')->default(200);
            $table->integer('default_height')->default(100);

            // Additional Settings
            $table->boolean('include_border')->default(false);
            $table->string('border_color', 7)->default('#000000');
            $table->string('background_color', 7)->nullable();

            // Metadata
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'is_active', 'is_default'], 'idx_user_signatures');
            $table->index(['user_id', 'signature_name'], 'idx_signature_name');
            $table->index(['user_id', 'last_used_at'], 'idx_last_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
