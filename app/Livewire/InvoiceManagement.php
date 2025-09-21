<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManagement extends Component
{
    use WithPagination;

    public $showModal = false;

    public $modalMode = 'create';

    public $selectedInvoice;

    public $searchTerm = '';

    public $statusFilter = 'all';

    public $invoiceForm = [
        'invoice_number' => '',
        'invoice_date' => '',
        'due_date' => '',
        'company_id' => '',
        'invoice_to_id' => '',
        'sub_total' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'balance' => 0,
        'grand_total' => 0,
        'currency' => 'KSH',
        'status' => 'draft',
        'notes' => '',
        'internal_notes' => '',
    ];

    public $items = [];

    public $clients = [];

    public $companies = [];

    public $showClientModal = false;

    public $confirmingDeletion = false;

    public $invoiceToDelete = null;

    public $clientForm = [
        'company_name' => '',
        'company_address' => '',
        'primary_phone' => '',
        'secondary_phone' => '',
        'email' => '',
        'website' => '',
        'mpesa_account' => '',
        'mpesa_holder_name' => '',
        'bank_name' => '',
        'bank_account' => '',
        'bank_holder_name' => '',
        'additional_notes' => '',
    ];

    protected function getInvoiceValidationRules(): array
    {
        return [
            'invoiceForm.invoice_number' => 'required|string|max:255',
            'invoiceForm.invoice_date' => 'required|date',
            'invoiceForm.due_date' => 'nullable|date|after_or_equal:invoiceForm.invoice_date',
            'invoiceForm.company_id' => 'required|exists:companies,id',
            'invoiceForm.invoice_to_id' => 'required|exists:invoice_tos,id',
            'invoiceForm.currency' => 'required|string|max:3',
            'invoiceForm.status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'invoiceForm.notes' => 'nullable|string',
            'invoiceForm.internal_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    protected function getClientValidationRules(): array
    {
        return [
            'clientForm.company_name' => 'required|string|max:255',
            'clientForm.company_address' => 'nullable|string',
            'clientForm.primary_phone' => 'nullable|string|max:255',
            'clientForm.secondary_phone' => 'nullable|string|max:255',
            'clientForm.email' => 'nullable|email|max:255',
            'clientForm.website' => 'nullable|url|max:255',
            'clientForm.mpesa_account' => 'nullable|string|max:255',
            'clientForm.mpesa_holder_name' => 'nullable|string|max:255',
            'clientForm.bank_name' => 'nullable|string|max:255',
            'clientForm.bank_account' => 'nullable|string|max:255',
            'clientForm.bank_holder_name' => 'nullable|string|max:255',
            'clientForm.additional_notes' => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->loadInitialData();
        $this->generateInvoiceNumber();
    }

    public function loadInitialData(): void
    {
        $this->companies = Company::where('user_id', Auth::id())->get();
        $this->clients = InvoiceTo::where('user_id', Auth::id())->get();

        if ($this->companies->isNotEmpty()) {
            $this->invoiceForm['company_id'] = $this->companies->first()->id;
        }
    }

    public function generateInvoiceNumber(): void
    {
        $lastInvoice = Invoice::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            $this->invoiceForm['invoice_number'] = 'MSDI '.$nextNumber;
        } else {
            $this->invoiceForm['invoice_number'] = 'MSDI 1001';
        }
    }

    public function createInvoice(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->generateInvoiceNumber();
        $this->invoiceForm['invoice_date'] = now()->format('Y-m-d');
        $this->invoiceForm['due_date'] = now()->addDays(30)->format('Y-m-d');
        $this->addItem();
        $this->showModal = true;
    }

    public function editInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            return;
        }

        $this->selectedInvoice = $invoice;
        $this->modalMode = 'edit';
        $this->loadInvoiceData($invoice);
        $this->showModal = true;
    }

    public function viewInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            return;
        }

        $this->selectedInvoice = $invoice;
        $this->modalMode = 'view';
        $this->loadInvoiceData($invoice);
        $this->showModal = true;
    }

    protected function loadInvoiceData(Invoice $invoice): void
    {
        $this->invoiceForm = [
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'company_id' => $invoice->company_id,
            'invoice_to_id' => $invoice->invoice_to_id,
            'sub_total' => $invoice->sub_total,
            'tax_amount' => $invoice->tax_amount,
            'discount_amount' => $invoice->discount_amount,
            'balance' => $invoice->balance,
            'grand_total' => $invoice->grand_total,
            'currency' => $invoice->currency,
            'status' => $invoice->status,
            'notes' => $invoice->notes,
            'internal_notes' => $invoice->internal_notes,
        ];

        $this->items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'service_name' => $item->service_name,
                'description' => $item->description,
                'period' => $item->period,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'discount_rate' => $item->discount_rate,
                'sort_order' => $item->sort_order,
            ];
        })->toArray();
    }

    public function saveInvoice(): void
    {
        try {
            $this->validate($this->getInvoiceValidationRules());

            $invoiceData = array_merge($this->invoiceForm, ['user_id' => Auth::id()]);

            if ($this->modalMode === 'create') {
                $invoice = Invoice::create($invoiceData);
                Log::info('Invoice created successfully', ['invoice_id' => $invoice->id, 'user_id' => Auth::id()]);
            } else {
                $invoice = $this->selectedInvoice;
                $invoice->update($invoiceData);
                $invoice->items()->delete();
                Log::info('Invoice updated successfully', ['invoice_id' => $invoice->id, 'user_id' => Auth::id()]);
            }

            foreach ($this->items as $index => $item) {
                $invoice->items()->create(array_merge($item, [
                    'sort_order' => $index + 1,
                ]));
            }

            $this->closeModal();
            $this->dispatch('invoice-saved');
            session()->flash('success', $this->modalMode === 'create' ? 'Invoice created successfully!' : 'Invoice updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Invoice validation failed', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to save invoice', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'mode' => $this->modalMode,
                'invoice_data' => $invoiceData ?? null,
            ]);
            session()->flash('error', 'Failed to save invoice. Please try again or contact support.');
        }
    }

    public function confirmDelete(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            session()->flash('error', 'Unauthorized action.');

            return;
        }

        $this->invoiceToDelete = $invoice;
        $this->confirmingDeletion = true;
    }

    public function deleteInvoice(): void
    {
        try {
            if (! $this->invoiceToDelete || $this->invoiceToDelete->user_id !== Auth::id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            $invoiceNumber = $this->invoiceToDelete->invoice_number;
            $this->invoiceToDelete->delete();

            Log::info('Invoice deleted successfully', [
                'invoice_id' => $this->invoiceToDelete->id,
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
            ]);

            session()->flash('success', 'Invoice deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $this->invoiceToDelete?->id,
                'user_id' => Auth::id(),
            ]);
            session()->flash('error', 'Failed to delete invoice. Please try again.');
        } finally {
            $this->confirmingDeletion = false;
            $this->invoiceToDelete = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->invoiceToDelete = null;
    }

    public function markAsPaid(Invoice $invoice): void
    {
        try {
            if ($invoice->user_id !== Auth::id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            if ($invoice->status !== 'sent') {
                session()->flash('error', 'Only sent invoices can be marked as paid.');

                return;
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'balance' => 0,
            ]);

            Log::info('Invoice marked as paid', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => Auth::id(),
            ]);

            session()->flash('success', 'Invoice marked as paid!');
        } catch (\Exception $e) {
            Log::error('Failed to mark invoice as paid', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);
            session()->flash('error', 'Failed to mark invoice as paid. Please try again.');
        }
    }

    public function markAsSent(Invoice $invoice): void
    {
        try {
            if ($invoice->user_id !== Auth::id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            if ($invoice->status !== 'draft') {
                session()->flash('error', 'Only draft invoices can be marked as sent.');

                return;
            }

            $invoice->update([
                'status' => 'sent',
            ]);

            Log::info('Invoice marked as sent', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => Auth::id(),
            ]);

            session()->flash('success', 'Invoice marked as sent!');
        } catch (\Exception $e) {
            Log::error('Failed to mark invoice as sent', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);
            session()->flash('error', 'Failed to mark invoice as sent. Please try again.');
        }
    }

    // TODO: Future Enhancement - Automatic Overdue Status Management
    //
    // Implementation Plan for Automatic Overdue Status:
    // 1. Create Artisan Command: 'app/Console/Commands/CheckOverdueInvoices.php'
    //    - Query all invoices where status='sent' AND due_date < now() AND balance > 0
    //    - Update status to 'overdue' for matching invoices
    //    - Log status changes for audit trail
    //
    // 2. Schedule Command in 'routes/console.php':
    //    Schedule::command('invoices:check-overdue')->daily();
    //
    // 3. Add Invoice Model method 'markAsOverdue()':
    //    - Validate invoice is eligible (sent status, past due date, has balance)
    //    - Update status to 'overdue'
    //    - Log status change with timestamp
    //
    // 4. Optional: Add email notifications for overdue invoices
    //    - Create notification class for overdue invoice alerts
    //    - Send to both issuer and client when invoice becomes overdue
    //    - Include payment reminders and due amount details
    //
    // 5. Add manual 'Mark as Overdue' action in UI for exceptional cases
    //    - Similar to markAsSent/markAsPaid methods
    //    - Only available for 'sent' invoices past due date
    //
    // Usage: This functionality will ensure invoices automatically transition
    // to 'overdue' status without manual intervention, improving workflow accuracy.

    public function addItem(): void
    {
        $this->items[] = [
            'service_name' => '',
            'description' => '',
            'period' => '',
            'quantity' => 1,
            'unit' => 'service',
            'unit_price' => 0,
            'tax_rate' => 0,
            'discount_rate' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function calculateTotals(): void
    {
        $subTotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($this->items as $item) {
            if (empty($item['quantity']) || empty($item['unit_price'])) {
                continue;
            }

            $lineSubtotal = $item['quantity'] * $item['unit_price'];
            $discountAmount = ($lineSubtotal * ($item['discount_rate'] ?? 0)) / 100;
            $afterDiscount = $lineSubtotal - $discountAmount;
            $taxAmount = ($afterDiscount * ($item['tax_rate'] ?? 0)) / 100;

            $subTotal += $lineSubtotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
        }

        $this->invoiceForm['sub_total'] = $subTotal;
        $this->invoiceForm['tax_amount'] = $totalTax;
        $this->invoiceForm['discount_amount'] = $totalDiscount;
        $this->invoiceForm['grand_total'] = $subTotal - $totalDiscount + $totalTax;
        $this->invoiceForm['balance'] = $this->invoiceForm['grand_total'];
    }

    public function updatedItems(): void
    {
        $this->calculateTotals();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function openClientModal(): void
    {
        $this->resetClientForm();
        $this->showClientModal = true;
    }

    public function closeClientModal(): void
    {
        $this->showClientModal = false;
        $this->resetClientForm();
    }

    public function saveClient(): void
    {
        try {
            $this->validate($this->getClientValidationRules());

            $clientData = array_merge($this->clientForm, ['user_id' => Auth::id()]);
            $newClient = InvoiceTo::create($clientData);

            Log::info('Client created successfully', [
                'client_id' => $newClient->id,
                'company_name' => $newClient->company_name,
                'user_id' => Auth::id(),
            ]);

            $this->clients = InvoiceTo::where('user_id', Auth::id())->get();
            $this->invoiceForm['invoice_to_id'] = $newClient->id;

            $this->closeClientModal();
            session()->flash('success', 'Client created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Client validation failed', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create client', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'client_data' => $this->clientForm,
            ]);
            session()->flash('error', 'Failed to create client. Please try again or contact support.');
        }
    }

    protected function resetForm(): void
    {
        $this->invoiceForm = [
            'invoice_number' => '',
            'invoice_date' => '',
            'due_date' => '',
            'company_id' => $this->companies->first()->id ?? '',
            'invoice_to_id' => '',
            'sub_total' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'balance' => 0,
            'grand_total' => 0,
            'currency' => 'KSH',
            'status' => 'draft',
            'notes' => '',
            'internal_notes' => '',
        ];
        $this->items = [];
        $this->selectedInvoice = null;
    }

    protected function resetClientForm(): void
    {
        $this->clientForm = [
            'company_name' => '',
            'company_address' => '',
            'primary_phone' => '',
            'secondary_phone' => '',
            'email' => '',
            'website' => '',
            'mpesa_account' => '',
            'mpesa_holder_name' => '',
            'bank_name' => '',
            'bank_account' => '',
            'bank_holder_name' => '',
            'additional_notes' => '',
        ];
    }

    public function render()
    {
        $invoices = Invoice::with(['company', 'invoiceTo', 'items'])
            ->where('user_id', Auth::id())
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', '%'.$this->searchTerm.'%')
                        ->orWhereHas('invoiceTo', function ($subq) {
                            $subq->where('company_name', 'like', '%'.$this->searchTerm.'%');
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.invoice-management', compact('invoices'));
    }

    public function generatePdf(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            return;
        }

        try {
            // Ensure storage directory exists
            $userDir = storage_path("app/public/invoices/{$invoice->user_id}");
            if (! file_exists($userDir)) {
                mkdir($userDir, 0755, true);
            }

            $path = $invoice->generatePdf();

            session()->flash('success', 'Invoice PDF generated successfully!');
            $this->dispatch('pdf-generated', ['path' => $path, 'url' => $invoice->getPdfUrl()]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate PDF: '.$e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            return;
        }

        try {
            $path = $invoice->getOrGeneratePdf();
            $fullPath = storage_path("app/public/{$path}");

            if (! file_exists($fullPath)) {
                session()->flash('error', 'PDF file not found. Please generate it again.');

                return;
            }

            $this->dispatch('download-pdf', ['url' => asset("storage/{$path}")]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download PDF: '.$e->getMessage());
        }
    }

    public function viewPdf(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            return;
        }

        try {
            $path = $invoice->getOrGeneratePdf();
            $this->dispatch('view-pdf', ['url' => asset("storage/{$path}")]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to view PDF: '.$e->getMessage());
        }
    }
}
