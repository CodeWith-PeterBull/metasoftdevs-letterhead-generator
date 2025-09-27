<?php

namespace App\Livewire;

use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SignatureManagement extends Component
{
    use WithFileUploads, WithPagination;

    public $showModal = false;

    public $modalMode = 'create';

    public $selectedSignature;

    public $searchTerm = '';

    public $signatureImage;

    public $stampImage;

    public $signatureForm = [
        'signature_name' => '',
        'description' => '',
        'is_default' => false,
        'is_active' => true,
        'full_name' => '',
        'position_title' => '',
        'initials' => '',
        'display_name' => true,
        'display_title' => true,
        'display_date' => true,
        'date_format' => 'd/m/Y',
        'font_family' => 'Arial',
        'font_size' => 'medium',
        'text_color' => '#000000',
        'default_position' => 'right',
        'default_width' => 200,
        'default_height' => 100,
        'include_border' => false,
        'border_color' => '#000000',
        'background_color' => '',
    ];

    protected $rules = [
        'signatureForm.signature_name' => 'required|string|max:255',
        'signatureForm.description' => 'nullable|string',
        'signatureForm.is_default' => 'boolean',
        'signatureForm.is_active' => 'boolean',
        'signatureForm.full_name' => 'required|string|max:255',
        'signatureForm.position_title' => 'nullable|string|max:255',
        'signatureForm.initials' => 'nullable|string|max:10',
        'signatureForm.display_name' => 'boolean',
        'signatureForm.display_title' => 'boolean',
        'signatureForm.display_date' => 'boolean',
        'signatureForm.date_format' => 'required|string|max:50',
        'signatureForm.font_family' => 'required|string|max:100',
        'signatureForm.font_size' => 'required|in:small,medium,large',
        'signatureForm.text_color' => 'required|string|max:7',
        'signatureForm.default_position' => 'required|in:left,center,right',
        'signatureForm.default_width' => 'required|integer|min:100|max:500',
        'signatureForm.default_height' => 'required|integer|min:50|max:300',
        'signatureForm.include_border' => 'boolean',
        'signatureForm.border_color' => 'required|string|max:7',
        'signatureForm.background_color' => 'nullable|string|max:7',
        'signatureImage' => 'nullable|image|max:2048',
        'stampImage' => 'nullable|image|max:2048',
    ];

    public function mount(): void
    {
        $this->signatureForm['full_name'] = Auth::user()->name;
    }

    public function createSignature(): void
    {
        $this->resetForm();
        $this->signatureForm['full_name'] = Auth::user()->name;
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function editSignature(DocumentSignature $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            return;
        }

        $this->selectedSignature = $signature;
        $this->modalMode = 'edit';
        $this->loadSignatureData($signature);
        $this->showModal = true;
    }

    public function viewSignature(DocumentSignature $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            return;
        }

        $this->selectedSignature = $signature;
        $this->modalMode = 'view';
        $this->loadSignatureData($signature);
        $this->showModal = true;
    }

    protected function loadSignatureData(DocumentSignature $signature): void
    {
        $this->signatureForm = [
            'signature_name' => $signature->signature_name,
            'description' => $signature->description,
            'is_default' => $signature->is_default,
            'is_active' => $signature->is_active,
            'full_name' => $signature->full_name,
            'position_title' => $signature->position_title,
            'initials' => $signature->initials,
            'display_name' => $signature->display_name,
            'display_title' => $signature->display_title,
            'display_date' => $signature->display_date,
            'date_format' => $signature->date_format,
            'font_family' => $signature->font_family,
            'font_size' => $signature->font_size,
            'text_color' => $signature->text_color,
            'default_position' => $signature->default_position,
            'default_width' => $signature->default_width,
            'default_height' => $signature->default_height,
            'include_border' => $signature->include_border,
            'border_color' => $signature->border_color,
            'background_color' => $signature->background_color,
        ];
    }

    public function saveSignature(): void
    {
        $this->validate();

        $signatureData = array_merge($this->signatureForm, ['user_id' => Auth::id()]);

        // Handle signature image upload
        if ($this->signatureImage) {
            $path = $this->signatureImage->store('signatures', 'public');
            $signatureData['signature_image_type'] = 'file';
            $signatureData['signature_image_data'] = $path;
            $signatureData['signature_image_width'] = 200;
            $signatureData['signature_image_height'] = 80;
        }

        // Handle stamp image upload
        if ($this->stampImage) {
            $path = $this->stampImage->store('stamps', 'public');
            $signatureData['stamp_image_type'] = 'file';
            $signatureData['stamp_image_data'] = $path;
            $signatureData['stamp_image_width'] = 60;
            $signatureData['stamp_image_height'] = 60;
        }

        if ($this->modalMode === 'create') {
            DocumentSignature::create($signatureData);
            session()->flash('success', 'Signature created successfully!');
        } else {
            // Delete old images if new ones are uploaded
            if ($this->signatureImage && $this->selectedSignature->signature_image_type === 'file') {
                Storage::disk('public')->delete($this->selectedSignature->signature_image_data);
            }
            if ($this->stampImage && $this->selectedSignature->stamp_image_type === 'file') {
                Storage::disk('public')->delete($this->selectedSignature->stamp_image_data);
            }

            $this->selectedSignature->update($signatureData);
            session()->flash('success', 'Signature updated successfully!');
        }

        $this->closeModal();
        $this->dispatch('signature-saved');
    }

    public function deleteSignature(DocumentSignature $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            session()->flash('error', 'Unauthorized action.');

            return;
        }

        // Check if this is the user's only signature
        $userSignatureCount = DocumentSignature::where('user_id', Auth::id())->count();
        if ($userSignatureCount <= 1) {
            session()->flash('error', 'Cannot delete your only signature. You must have at least one signature.');

            return;
        }

        try {
            $signatureName = $signature->signature_name;
            $wasDefault = $signature->is_default;

            $signature->delete();

            if ($wasDefault) {
                session()->flash('success', "Signature '{$signatureName}' deleted successfully! Another signature has been automatically set as default.");
            } else {
                session()->flash('success', "Signature '{$signatureName}' deleted successfully!");
            }

            $this->dispatch('signature-deleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete signature. Please try again.');
        }
    }

    public function setAsDefault(DocumentSignature $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            session()->flash('error', 'Unauthorized action.');

            return;
        }

        // If already default, no need to change
        if ($signature->is_default) {
            session()->flash('info', "Signature '{$signature->signature_name}' is already the default signature.");

            return;
        }

        // Set this signature as default (model boot method will handle removing default from others)
        $signature->update(['is_default' => true]);

        session()->flash('success', "Signature '{$signature->signature_name}' set as default!");

        $this->dispatch('signature-updated');
    }

    public function toggleActive(DocumentSignature $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            return;
        }

        $signature->update(['is_active' => ! $signature->is_active]);

        $status = $signature->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Signature '{$signature->signature_name}' {$status}!");

        $this->dispatch('signature-updated');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->reset(['signatureImage', 'stampImage']);
    }

    protected function resetForm(): void
    {
        $this->signatureForm = [
            'signature_name' => '',
            'description' => '',
            'is_default' => false,
            'is_active' => true,
            'full_name' => '',
            'position_title' => '',
            'initials' => '',
            'display_name' => true,
            'display_title' => true,
            'display_date' => true,
            'date_format' => 'd/m/Y',
            'font_family' => 'Arial',
            'font_size' => 'medium',
            'text_color' => '#000000',
            'default_position' => 'right',
            'default_width' => 200,
            'default_height' => 100,
            'include_border' => false,
            'border_color' => '#000000',
            'background_color' => '',
        ];
        $this->selectedSignature = null;
    }

    public function render()
    {
        $signatures = DocumentSignature::where('user_id', Auth::id())
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('signature_name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('full_name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('position_title', 'like', '%'.$this->searchTerm.'%');
                });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('signature_name')
            ->paginate(10);

        return view('livewire.signature-management', compact('signatures'));
    }
}
