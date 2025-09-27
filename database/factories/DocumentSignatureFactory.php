<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentSignature>
 */
class DocumentSignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'signature_name' => fake()->words(2, true).' Signature',
            'description' => fake()->sentence(),
            'is_default' => false,
            'is_active' => true,
            'full_name' => fake()->name(),
            'position_title' => fake()->jobTitle(),
            'initials' => strtoupper(fake()->lexify('??')),
            'signature_image_type' => fake()->randomElement(['base64', 'file']),
            'signature_image_data' => null,
            'signature_image_width' => fake()->numberBetween(100, 300),
            'signature_image_height' => fake()->numberBetween(50, 150),
            'stamp_image_type' => fake()->randomElement(['base64', 'file', null]),
            'stamp_image_data' => null,
            'stamp_image_width' => fake()->numberBetween(40, 80),
            'stamp_image_height' => fake()->numberBetween(40, 80),
            'display_name' => fake()->boolean(80),
            'display_title' => fake()->boolean(70),
            'display_date' => fake()->boolean(60),
            'date_format' => fake()->randomElement(['d/m/Y', 'Y-m-d', 'M d, Y', 'd-m-Y']),
            'font_family' => fake()->randomElement(['Arial', 'Times New Roman', 'Helvetica', 'Georgia']),
            'font_size' => fake()->randomElement(['small', 'medium', 'large']),
            'text_color' => fake()->randomElement(['#000000', '#333333', '#1a1a1a', '#2c3e50']),
            'default_position' => fake()->randomElement(['left', 'center', 'right']),
            'default_width' => fake()->numberBetween(150, 250),
            'default_height' => fake()->numberBetween(80, 120),
            'include_border' => fake()->boolean(30),
            'border_color' => fake()->randomElement(['#000000', '#cccccc', '#999999']),
            'background_color' => fake()->boolean(20) ? fake()->randomElement(['#ffffff', '#f8f9fa', '#e9ecef']) : null,
            'usage_count' => fake()->numberBetween(0, 50),
            'last_used_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 year') : null,
        ];
    }

    /**
     * Indicate that the signature is a default signature.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the signature is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the signature has a signature image.
     */
    public function withSignatureImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'signature_image_data' => 'data:image/png;base64,'.base64_encode('fake_image_data'),
            'signature_image_type' => 'base64',
        ]);
    }

    /**
     * Indicate that the signature has a stamp image.
     */
    public function withStampImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'stamp_image_data' => 'data:image/png;base64,'.base64_encode('fake_stamp_data'),
            'stamp_image_type' => 'base64',
        ]);
    }

    /**
     * Create a complete signature with both signature and stamp images.
     */
    public function complete(): static
    {
        return $this->withSignatureImage()->withStampImage();
    }
}
