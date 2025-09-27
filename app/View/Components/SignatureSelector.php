<?php

namespace App\View\Components;

use App\Models\DocumentSignature;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class SignatureSelector extends Component
{
    public Collection $signatures;

    public ?DocumentSignature $selectedSignature;

    public string $inputName;

    public ?int $selectedId;

    public bool $showPreview;

    public string $layout;

    public bool $allowNone;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?Collection $signatures = null,
        ?DocumentSignature $selectedSignature = null,
        string $inputName = 'signature_id',
        ?int $selectedId = null,
        bool $showPreview = true,
        string $layout = 'grid',
        bool $allowNone = false
    ) {
        // Get user's active signatures if not provided
        $this->signatures = $signatures ?? DocumentSignature::active()
            ->forUser(Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('signature_name')
            ->get();

        $this->selectedSignature = $selectedSignature;
        $this->inputName = $inputName;
        $this->selectedId = $selectedId ?? $selectedSignature?->id;
        $this->showPreview = $showPreview;
        $this->layout = $layout; // 'grid', 'list', 'dropdown'
        $this->allowNone = $allowNone;

        // Auto-select default signature if none selected
        if (! $this->selectedId && ! $this->allowNone) {
            $defaultSignature = $this->signatures->firstWhere('is_default', true);
            $this->selectedId = $defaultSignature?->id;
            $this->selectedSignature = $defaultSignature;
        }
    }

    /**
     * Get the currently selected signature model.
     */
    public function getSelectedSignature(): ?DocumentSignature
    {
        if ($this->selectedSignature) {
            return $this->selectedSignature;
        }

        if ($this->selectedId) {
            return $this->signatures->firstWhere('id', $this->selectedId);
        }

        return null;
    }

    /**
     * Check if a signature is currently selected.
     */
    public function isSelected(DocumentSignature $signature): bool
    {
        return $this->selectedId === $signature->id;
    }

    /**
     * Get CSS class for layout.
     */
    public function getLayoutClass(): string
    {
        return match ($this->layout) {
            'grid' => 'signature-selector-grid',
            'list' => 'signature-selector-list',
            'dropdown' => 'signature-selector-dropdown',
            default => 'signature-selector-grid'
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.signature-selector');
    }
}
