<?php

namespace Tests\Feature;

use App\Livewire\InvoiceManagement;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_management_component_renders(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'Test Company',
            'address' => 'Test Address',
            'primary_color' => '#000000',
            'default_template' => 'classic',
            'default_paper_size' => 'us_letter',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(InvoiceManagement::class)
            ->assertStatus(200)
            ->assertSee('Invoice Management')
            ->assertSee('Create Invoice');
    }

    public function test_validation_errors_are_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(InvoiceManagement::class)
            ->call('createInvoice')
            ->set('invoiceForm.invoice_number', '') // Required field
            ->set('invoiceForm.invoice_date', '')   // Required field
            ->set('invoiceForm.company_id', '')     // Clear the auto-populated value
            ->set('items', [])                      // Clear the auto-added item
            ->call('saveInvoice')
            ->assertHasErrors([
                'invoiceForm.invoice_number',
                'invoiceForm.invoice_date',
                'invoiceForm.company_id',
                'invoiceForm.invoice_to_id',
                'items',
            ]);
    }
}
