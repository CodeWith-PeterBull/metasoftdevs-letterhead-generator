<?php

namespace App\Livewire;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;

class CompanyManagement extends Component
{
    use WithFileUploads, WithPagination;

    // Search and pagination
    public $search = '';
    public $perPage = 10;

    // Modal state
    public $showModal = false;
    public $modalTitle = '';
    public $isEditing = false;

    // Form properties
    #[Rule('required|min:2|max:255')]
    public $name = '';

    #[Rule('required|min:5')]
    public $address = '';

    #[Rule('nullable|max:20')]
    public $phone_1 = '';

    #[Rule('nullable|max:20')]
    public $phone_2 = '';

    #[Rule('nullable|email|max:255')]
    public $email_1 = '';

    #[Rule('nullable|email|max:255')]
    public $email_2 = '';

    #[Rule('nullable|url|max:255')]
    public $website = '';

    #[Rule('nullable|url|max:255')]
    public $linkedin_url = '';

    #[Rule('nullable|max:50')]
    public $twitter_handle = '';

    #[Rule('nullable|url|max:255')]
    public $facebook_url = '';

    #[Rule('nullable|image|max:2048')]
    public $logo = '';

    #[Rule('required|string|size:7')]
    public $primary_color = '#000000';

    #[Rule('nullable|max:100')]
    public $industry = '';

    #[Rule('nullable|max:1000')]
    public $description = '';

    #[Rule('required|in:classic,modern_green,corporate_blue,elegant_gray')]
    public $default_template = 'classic';

    #[Rule('required|in:us_letter,a4,legal')]
    public $default_paper_size = 'us_letter';

    #[Rule('boolean')]
    public $include_social_media = false;

    #[Rule('boolean')]
    public $include_registration_details = false;

    #[Rule('boolean')]
    public $is_active = true;

    #[Rule('boolean')]
    public $is_default = false;

    // Current company being edited
    public ?Company $editingCompany = null;

    // Confirmation state
    public $confirmingDeletion = false;
    public ?Company $companyToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $companies = Company::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('industry', 'like', '%' . $this->search . '%')
                      ->orWhere('email_1', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.company-management', [
            'companies' => $companies,
            'templateOptions' => [
                'classic' => 'Classic',
                'modern_green' => 'Modern Green',
                'corporate_blue' => 'Corporate Blue',
                'elegant_gray' => 'Elegant Gray',
            ],
            'paperSizeOptions' => [
                'us_letter' => 'US Letter (8.5" x 11")',
                'a4' => 'A4 (210 x 297 mm)',
                'legal' => 'Legal (8.5" x 14")',
            ],
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalTitle = 'Create New Company';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(Company $company)
    {
        $this->editingCompany = $company;
        $this->fillForm($company);
        $this->modalTitle = 'Edit Company';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'name' => $this->name,
            'address' => $this->address,
            'phone_1' => $this->phone_1,
            'phone_2' => $this->phone_2,
            'email_1' => $this->email_1,
            'email_2' => $this->email_2,
            'website' => $this->website,
            'linkedin_url' => $this->linkedin_url,
            'twitter_handle' => $this->twitter_handle,
            'facebook_url' => $this->facebook_url,
            'primary_color' => $this->primary_color,
            'industry' => $this->industry,
            'description' => $this->description,
            'default_template' => $this->default_template,
            'default_paper_size' => $this->default_paper_size,
            'include_social_media' => $this->include_social_media,
            'include_registration_details' => $this->include_registration_details,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
        ];

        if ($this->isEditing) {
            $this->editingCompany->update($data);
            $company = $this->editingCompany;
            $message = 'Company updated successfully!';
        } else {
            // If this is the first company, make it default
            if (Auth::user()->companies()->count() === 0) {
                $data['is_default'] = true;
            }
            
            $company = Company::create($data);
            $message = 'Company created successfully!';
        }

        // Handle logo upload
        if ($this->logo) {
            $company->clearMediaCollection('logo');
            $company->addMedia($this->logo->getRealPath())
                ->usingName($company->name . ' Logo')
                ->usingFileName($this->logo->getClientOriginalName())
                ->toMediaCollection('logo');
        }

        // Handle default company logic
        if ($this->is_default) {
            $company->setAsDefault();
        }

        $this->dispatch('company-updated');
        $this->closeModal();
        
        session()->flash('success', $message);
    }

    public function confirmDelete(Company $company)
    {
        // Check if it's the last active company
        $activeCompaniesCount = Auth::user()->companies()->where('is_active', true)->count();
        
        if ($activeCompaniesCount <= 1 && $company->is_active) {
            session()->flash('error', 'Cannot delete the last active company. Please create another company first.');
            return;
        }

        $this->companyToDelete = $company;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->companyToDelete) {
            // If deleting default company, assign default to another active company
            if ($this->companyToDelete->is_default) {
                $newDefault = Auth::user()->companies()
                    ->where('is_active', true)
                    ->where('id', '!=', $this->companyToDelete->id)
                    ->first();
                
                if ($newDefault) {
                    $newDefault->setAsDefault();
                }
            }

            $this->companyToDelete->delete();
            $this->dispatch('company-updated');
            
            session()->flash('success', 'Company deleted successfully!');
        }

        $this->confirmingDeletion = false;
        $this->companyToDelete = null;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->companyToDelete = null;
    }

    public function toggleActive(Company $company)
    {
        // Check if trying to deactivate the last active company
        $activeCompaniesCount = Auth::user()->companies()->where('is_active', true)->count();
        
        if ($activeCompaniesCount <= 1 && $company->is_active) {
            session()->flash('error', 'Cannot deactivate the last active company.');
            return;
        }

        $company->update(['is_active' => !$company->is_active]);

        // If deactivating default company, assign default to another active company
        if (!$company->is_active && $company->is_default) {
            $newDefault = Auth::user()->companies()
                ->where('is_active', true)
                ->where('id', '!=', $company->id)
                ->first();
            
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        $this->dispatch('company-updated');
        
        $status = $company->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Company {$status} successfully!");
    }

    public function setAsDefault(Company $company)
    {
        if (!$company->is_active) {
            session()->flash('error', 'Cannot set inactive company as default.');
            return;
        }

        $company->setAsDefault();
        $this->dispatch('company-updated');
        
        session()->flash('success', 'Default company updated successfully!');
    }

    public function duplicateCompany(Company $company)
    {
        $newCompany = $company->replicate([
            'is_default',
            'last_used_at',
            'created_at',
            'updated_at',
        ]);
        
        $newCompany->name = $company->name . ' - Copy';
        $newCompany->is_default = false;
        $newCompany->save();

        // Copy logo if exists
        if ($company->hasMedia('logo')) {
            $logoMedia = $company->getFirstMedia('logo');
            $newCompany->addMedia($logoMedia->getPath())
                ->usingName($newCompany->name . ' Logo')
                ->usingFileName($logoMedia->file_name)
                ->toMediaCollection('logo');
        }

        $this->dispatch('company-updated');
        session()->flash('success', 'Company duplicated successfully!');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->address = '';
        $this->phone_1 = '';
        $this->phone_2 = '';
        $this->email_1 = '';
        $this->email_2 = '';
        $this->website = '';
        $this->linkedin_url = '';
        $this->twitter_handle = '';
        $this->facebook_url = '';
        $this->logo = '';
        $this->primary_color = '#000000';
        $this->industry = '';
        $this->description = '';
        $this->default_template = 'classic';
        $this->default_paper_size = 'us_letter';
        $this->include_social_media = false;
        $this->include_registration_details = false;
        $this->is_active = true;
        $this->is_default = false;
        $this->editingCompany = null;
    }

    private function fillForm(Company $company)
    {
        $this->name = $company->name;
        $this->address = $company->address;
        $this->phone_1 = $company->phone_1;
        $this->phone_2 = $company->phone_2;
        $this->email_1 = $company->email_1;
        $this->email_2 = $company->email_2;
        $this->website = $company->website;
        $this->linkedin_url = $company->linkedin_url;
        $this->twitter_handle = $company->twitter_handle;
        $this->facebook_url = $company->facebook_url;
        $this->primary_color = $company->primary_color;
        $this->industry = $company->industry;
        $this->description = $company->description;
        $this->default_template = $company->default_template;
        $this->default_paper_size = $company->default_paper_size;
        $this->include_social_media = $company->include_social_media;
        $this->include_registration_details = $company->include_registration_details;
        $this->is_active = $company->is_active;
        $this->is_default = $company->is_default;
    }
}