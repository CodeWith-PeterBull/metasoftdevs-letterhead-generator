<?php

namespace Database\Seeders;

use App\Models\DocumentSignature;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Creating a test user first.');
            $users = collect([User::factory()->create()]);
        }

        // Create signatures for each user
        foreach ($users as $user) {
            // Create a default signature for each user
            DocumentSignature::factory()
                ->default()
                ->withSignatureImage()
                ->create([
                    'user_id' => $user->id,
                    'signature_name' => $user->name."'s Default Signature",
                    'full_name' => $user->name,
                    'description' => 'Default signature for '.$user->name,
                ]);

            // Create 2-3 additional signatures for each user
            DocumentSignature::factory()
                ->count(rand(2, 3))
                ->create([
                    'user_id' => $user->id,
                ]);

            // Create one complete signature (with both signature and stamp)
            DocumentSignature::factory()
                ->complete()
                ->create([
                    'user_id' => $user->id,
                    'signature_name' => $user->name."'s Official Signature",
                    'full_name' => $user->name,
                    'description' => 'Official signature with stamp for '.$user->name,
                    'position_title' => 'Director',
                    'display_name' => true,
                    'display_title' => true,
                    'display_date' => true,
                    'include_border' => true,
                ]);

            $this->command->info("Created signatures for user: {$user->name}");
        }

        $totalSignatures = DocumentSignature::count();
        $this->command->info("Created {$totalSignatures} document signatures in total.");
    }
}
