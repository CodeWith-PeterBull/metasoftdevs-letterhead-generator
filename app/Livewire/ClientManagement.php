<?php

namespace App\Livewire;

use App\Models\InvoiceTo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ClientManagement extends Component
{
    use WithPagination;

    public $showModal = false;

    public $modalMode = 'create';

    public $selectedClient;

    public $searchTerm = '';

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

    protected $rules = [
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

    public function createClient(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function editClient(InvoiceTo $client): void
    {
        if ($client->user_id !== Auth::id()) {
            return;
        }

        $this->selectedClient = $client;
        $this->modalMode = 'edit';
        $this->loadClientData($client);
        $this->showModal = true;
    }

    public function viewClient(InvoiceTo $client): void
    {
        if ($client->user_id !== Auth::id()) {
            return;
        }

        $this->selectedClient = $client;
        $this->modalMode = 'view';
        $this->loadClientData($client);
        $this->showModal = true;
    }

    protected function loadClientData(InvoiceTo $client): void
    {
        $this->clientForm = [
            'company_name' => $client->company_name,
            'company_address' => $client->company_address,
            'primary_phone' => $client->primary_phone,
            'secondary_phone' => $client->secondary_phone,
            'email' => $client->email,
            'website' => $client->website,
            'mpesa_account' => $client->mpesa_account,
            'mpesa_holder_name' => $client->mpesa_holder_name,
            'bank_name' => $client->bank_name,
            'bank_account' => $client->bank_account,
            'bank_holder_name' => $client->bank_holder_name,
            'additional_notes' => $client->additional_notes,
        ];
    }

    public function saveClient(): void
    {
        $this->validate();

        $clientData = array_merge($this->clientForm, ['user_id' => Auth::id()]);

        if ($this->modalMode === 'create') {
            InvoiceTo::create($clientData);
            session()->flash('success', 'Client created successfully!');
        } else {
            $this->selectedClient->update($clientData);
            session()->flash('success', 'Client updated successfully!');
        }

        $this->closeModal();
        $this->dispatch('client-saved');
    }

    public function deleteClient(InvoiceTo $client): void
    {
        if ($client->user_id !== Auth::id()) {
            return;
        }

        $client->delete();
        session()->flash('success', 'Client deleted successfully!');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
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
        $this->selectedClient = null;
    }

    public function render()
    {
        $clients = InvoiceTo::where('user_id', Auth::id())
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('company_name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('email', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('primary_phone', 'like', '%'.$this->searchTerm.'%');
                });
            })
            ->orderBy('company_name')
            ->paginate(10);

        return view('livewire.client-management', compact('clients'));
    }
}
