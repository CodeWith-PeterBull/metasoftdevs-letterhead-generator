<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and companies
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        foreach ($users as $user) {
            // Get user's companies
            $companies = Company::where('user_id', $user->id)->get();

            if ($companies->isEmpty()) {
                continue;
            }

            // Create sample clients for this user
            $clients = $this->createSampleClients($user);

            // Create sample invoices for this user
            $this->createSampleInvoices($user, $companies, $clients);
        }
    }

    /**
     * Create sample clients for a user
     */
    private function createSampleClients(User $user): array
    {
        $clientsData = [
            [
                'company_name' => 'REGENT AUCTIONEERS LTD',
                'company_address' => "Plot 32, Kampala Road\nNakuru, Kenya\nP.O. Box 1234-20100",
                'primary_phone' => '+254 712 345 678',
                'secondary_phone' => '+254 733 456 789',
                'email' => 'info@regentauctioneers.co.ke',
                'website' => 'https://regentauctioneers.co.ke',
                'mpesa_account' => '+254 712 345 678',
                'mpesa_holder_name' => 'REGENT AUCTIONEERS LTD',
                'bank_name' => 'KCB Bank',
                'bank_account' => '1234567890',
                'bank_holder_name' => 'REGENT AUCTIONEERS LTD',
                'additional_notes' => 'Major auction house with established payment terms.',
            ],
            [
                'company_name' => 'TECH SOLUTIONS KENYA',
                'company_address' => "Westlands Business Park\nNairobi, Kenya\nP.O. Box 5678-00100",
                'primary_phone' => '+254 720 111 222',
                'email' => 'info@techsolutions.co.ke',
                'website' => 'https://techsolutions.co.ke',
                'mpesa_account' => '+254 720 111 222',
                'mpesa_holder_name' => 'TECH SOLUTIONS KENYA',
                'bank_name' => 'Equity Bank',
                'bank_account' => '0987654321',
                'bank_holder_name' => 'TECH SOLUTIONS KENYA LTD',
            ],
            [
                'company_name' => 'MOMBASA TRADING CO',
                'company_address' => "Moi Avenue\nMombasa, Kenya\nP.O. Box 9876-80100",
                'primary_phone' => '+254 711 333 444',
                'email' => 'trading@mombasa.co.ke',
                'mpesa_account' => '+254 711 333 444',
                'mpesa_holder_name' => 'MOMBASA TRADING CO',
                'additional_notes' => 'Coastal trading company, prefers MPESA payments.',
            ],
        ];

        $clients = [];
        foreach ($clientsData as $clientData) {
            $clients[] = InvoiceTo::create(array_merge($clientData, [
                'user_id' => $user->id,
            ]));
        }

        return $clients;
    }

    /**
     * Create sample invoices for a user
     */
    private function createSampleInvoices(User $user, $companies, array $clients): void
    {
        $company = $companies->first();
        $userPrefix = $user->id * 1000; // Generate unique invoice numbers per user

        // Invoice 1 - Recent paid invoice
        $invoice1 = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'MSDI '.($userPrefix + 1),
            'invoice_date' => Carbon::now()->subDays(15),
            'due_date' => Carbon::now()->subDays(15)->addDays(30),
            'company_id' => $company->id,
            'invoice_to_id' => $clients[0]->id,
            'currency' => 'KSH',
            'status' => 'paid',
            'notes' => 'Website maintenance and hosting services for April 2025.',
            'paid_at' => Carbon::now()->subDays(5),
            'payment_method' => 'MPESA',
            'payment_reference' => 'QRX123456',
        ]);

        // Invoice 1 Items
        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'service_name' => 'Website Maintenance',
            'description' => 'Monthly website maintenance including security updates, backups, and performance optimization.',
            'period' => 'April 2025',
            'quantity' => 1,
            'unit' => 'service',
            'unit_price' => 15000.00,
            'tax_rate' => 16.00,
            'sort_order' => 1,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'service_name' => 'Web Hosting',
            'description' => 'Premium web hosting with SSL certificate and daily backups.',
            'period' => 'April 2025',
            'quantity' => 1,
            'unit' => 'service',
            'unit_price' => 8000.00,
            'tax_rate' => 16.00,
            'sort_order' => 2,
        ]);

        // Invoice 2 - Current sent invoice
        $invoice2 = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'MSDI '.($userPrefix + 2),
            'invoice_date' => Carbon::now()->subDays(7),
            'due_date' => Carbon::now()->addDays(23),
            'company_id' => $company->id,
            'invoice_to_id' => $clients[1]->id,
            'currency' => 'KSH',
            'status' => 'sent',
            'notes' => 'Custom software development and consultation services.',
        ]);

        // Invoice 2 Items
        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'service_name' => 'Software Development',
            'description' => 'Custom inventory management system development.',
            'quantity' => 40,
            'unit' => 'hours',
            'unit_price' => 2500.00,
            'tax_rate' => 16.00,
            'sort_order' => 1,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'service_name' => 'Project Management',
            'description' => 'Project coordination and client consultation.',
            'quantity' => 10,
            'unit' => 'hours',
            'unit_price' => 1800.00,
            'tax_rate' => 16.00,
            'sort_order' => 2,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'service_name' => 'Testing & QA',
            'description' => 'System testing and quality assurance.',
            'quantity' => 15,
            'unit' => 'hours',
            'unit_price' => 2000.00,
            'tax_rate' => 16.00,
            'sort_order' => 3,
        ]);

        // Invoice 3 - Draft invoice
        $invoice3 = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'MSDI '.($userPrefix + 3),
            'invoice_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'company_id' => $company->id,
            'invoice_to_id' => $clients[2]->id,
            'currency' => 'KSH',
            'status' => 'draft',
            'notes' => 'E-commerce platform setup and configuration.',
        ]);

        // Invoice 3 Items
        InvoiceItem::create([
            'invoice_id' => $invoice3->id,
            'service_name' => 'E-commerce Setup',
            'description' => 'Complete e-commerce platform installation and configuration.',
            'quantity' => 1,
            'unit' => 'project',
            'unit_price' => 45000.00,
            'tax_rate' => 16.00,
            'sort_order' => 1,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice3->id,
            'service_name' => 'Payment Gateway Integration',
            'description' => 'Integration with MPESA and credit card payment gateways.',
            'quantity' => 1,
            'unit' => 'service',
            'unit_price' => 12000.00,
            'tax_rate' => 16.00,
            'sort_order' => 2,
        ]);

        // Invoice 4 - Overdue invoice
        if (count($clients) > 0) {
            $invoice4 = Invoice::create([
                'user_id' => $user->id,
                'invoice_number' => 'MSDI '.($userPrefix + 4),
                'invoice_date' => Carbon::now()->subDays(45),
                'due_date' => Carbon::now()->subDays(15),
                'company_id' => $company->id,
                'invoice_to_id' => $clients[0]->id,
                'currency' => 'KSH',
                'status' => 'overdue',
                'notes' => 'Logo design and branding services. Payment is now overdue.',
            ]);

            // Invoice 4 Items
            InvoiceItem::create([
                'invoice_id' => $invoice4->id,
                'service_name' => 'Logo Design',
                'description' => 'Professional logo design with multiple concepts and revisions.',
                'quantity' => 1,
                'unit' => 'project',
                'unit_price' => 25000.00,
                'tax_rate' => 16.00,
                'sort_order' => 1,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice4->id,
                'service_name' => 'Brand Guidelines',
                'description' => 'Comprehensive brand guidelines document.',
                'quantity' => 1,
                'unit' => 'document',
                'unit_price' => 8000.00,
                'tax_rate' => 16.00,
                'sort_order' => 2,
            ]);
        }

        $this->command->info("Created sample invoices for user: {$user->name}");
    }
}
