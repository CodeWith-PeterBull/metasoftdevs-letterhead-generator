<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('email', '!=', 'admin@metasoftletterheads.com')->get();

        $companyTemplates = [
            [
                'name' => 'TechCorp Solutions',
                'address' => '123 Technology Drive, Silicon Valley, CA 94025, USA',
                'phone_1' => '+1-555-0123',
                'phone_2' => '+1-555-0124',
                'email_1' => 'info@techcorp.com',
                'email_2' => 'contact@techcorp.com',
                'website' => 'https://techcorp.com',
                'linkedin_url' => 'https://linkedin.com/company/techcorp',
                'twitter_handle' => 'techcorp',
                'facebook_url' => 'https://facebook.com/techcorp',
                'primary_color' => '#007bff',
                'industry' => 'Technology',
                'description' => 'Leading provider of innovative technology solutions for modern businesses.',
                'default_template' => 'modern_green',
                'default_paper_size' => 'us_letter',
                'include_social_media' => true,
                'include_registration_details' => false,
                'is_active' => true,
                'is_default' => true,
                'last_used_at' => now(),
            ],
            [
                'name' => 'Green Energy Co',
                'address' => '456 Renewable Street, Austin, TX 78701, USA',
                'phone_1' => '+1-555-0456',
                'email_1' => 'hello@greenenergy.com',
                'website' => 'https://greenenergy.com',
                'linkedin_url' => 'https://linkedin.com/company/greenenergy',
                'primary_color' => '#28a745',
                'industry' => 'Renewable Energy',
                'description' => 'Sustainable energy solutions for a cleaner tomorrow.',
                'default_template' => 'corporate_blue',
                'default_paper_size' => 'a4',
                'include_social_media' => true,
                'include_registration_details' => true,
                'is_active' => true,
                'is_default' => false,
                'last_used_at' => now()->subDays(5),
            ],
        ];

        $additionalCompanies = [
            [
                'name' => 'Creative Design Studio',
                'address' => '789 Art Avenue, New York, NY 10001, USA',
                'phone_1' => '+1-555-0789',
                'email_1' => 'studio@creative.com',
                'website' => 'https://creativestudio.com',
                'primary_color' => '#e83e8c',
                'industry' => 'Design & Marketing',
                'description' => 'Creative solutions for brands that want to stand out.',
                'default_template' => 'elegant_gray',
                'default_paper_size' => 'us_letter',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Metro Consulting',
                'address' => '321 Business Blvd, Chicago, IL 60601, USA',
                'phone_1' => '+1-555-0321',
                'email_1' => 'consult@metro.com',
                'website' => 'https://metroconsulting.com',
                'primary_color' => '#6f42c1',
                'industry' => 'Business Consulting',
                'description' => 'Strategic consulting services for growing businesses.',
                'default_template' => 'classic',
                'default_paper_size' => 'legal',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'HealthTech Innovations',
                'address' => '654 Medical Center Dr, Boston, MA 02101, USA',
                'phone_1' => '+1-555-0654',
                'email_1' => 'info@healthtech.com',
                'website' => 'https://healthtech.com',
                'primary_color' => '#17a2b8',
                'industry' => 'Healthcare Technology',
                'description' => 'Innovative healthcare solutions powered by technology.',
                'default_template' => 'modern_green',
                'default_paper_size' => 'a4',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Global Logistics Ltd',
                'address' => '987 Shipping Lane, Miami, FL 33101, USA',
                'phone_1' => '+1-555-0987',
                'email_1' => 'logistics@global.com',
                'website' => 'https://globallogistics.com',
                'primary_color' => '#fd7e14',
                'industry' => 'Logistics & Transportation',
                'description' => 'Worldwide shipping and logistics solutions.',
                'default_template' => 'corporate_blue',
                'default_paper_size' => 'us_letter',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($users as $user) {
            // Create 2-3 companies per user
            $numCompanies = rand(2, 3);
            $isFirstCompany = true;

            for ($i = 0; $i < $numCompanies; $i++) {
                $template = $i < 2 ? $companyTemplates[$i] : $additionalCompanies[array_rand($additionalCompanies)];
                
                // Customize the template for each user
                $companyData = $template;
                $companyData['user_id'] = $user->id;
                
                // Ensure only first company is default
                $companyData['is_default'] = $isFirstCompany;
                $isFirstCompany = false;

                // Randomize some fields
                $companyData['name'] = $template['name'] . ' - ' . $user->name;
                $companyData['email_1'] = strtolower(str_replace(' ', '.', $user->name)) . '@' . strtolower(str_replace(' ', '', $template['name'])) . '.com';
                
                Company::create($companyData);
            }
        }
    }
}
