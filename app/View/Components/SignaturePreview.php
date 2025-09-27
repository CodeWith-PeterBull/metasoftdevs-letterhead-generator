<?php

namespace App\View\Components;

use App\Models\DocumentSignature;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SignaturePreview extends Component
{
    public DocumentSignature $signature;

    public string $size;

    public bool $showName;

    public bool $showTitle;

    public bool $interactive;

    /**
     * Create a new component instance.
     */
    public function __construct(
        DocumentSignature $signature,
        string $size = 'medium',
        bool $showName = true,
        bool $showTitle = true,
        bool $interactive = false
    ) {
        $this->signature = $signature;
        $this->size = $size; // 'small', 'medium', 'large'
        $this->showName = $showName;
        $this->showTitle = $showTitle;
        $this->interactive = $interactive;
    }

    /**
     * Get container CSS classes based on size.
     */
    public function getContainerClass(): string
    {
        $baseClass = 'signature-preview';

        $sizeClass = match ($this->size) {
            'small' => 'signature-preview-sm',
            'large' => 'signature-preview-lg',
            default => 'signature-preview-md'
        };

        $interactiveClass = $this->interactive ? 'signature-preview-interactive' : '';

        return trim("$baseClass $sizeClass $interactiveClass");
    }

    /**
     * Get image dimensions based on size.
     */
    public function getImageDimensions(): array
    {
        return match ($this->size) {
            'small' => ['width' => '80px', 'height' => '32px'],
            'large' => ['width' => '160px', 'height' => '64px'],
            default => ['width' => '120px', 'height' => '48px']
        };
    }

    /**
     * Get font size based on size setting.
     */
    public function getFontSize(): string
    {
        return match ($this->size) {
            'small' => '10px',
            'large' => '14px',
            default => '12px'
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.signature-preview');
    }
}
