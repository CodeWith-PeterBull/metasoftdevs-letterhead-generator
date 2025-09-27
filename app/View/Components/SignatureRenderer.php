<?php

namespace App\View\Components;

use App\Models\DocumentSignature;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class SignatureRenderer extends Component
{
    public DocumentSignature $signature;

    public string $renderStyle;

    public array $options;

    public bool $showDate;

    public ?string $customDate;

    /**
     * Create a new component instance.
     */
    public function __construct(
        DocumentSignature $signature,
        string $renderStyle = 'full',
        array $options = [],
        bool $showDate = true,
        ?string $customDate = null
    ) {
        $this->signature = $signature;
        $this->renderStyle = $renderStyle;
        $this->options = array_merge([
            'width' => $signature->default_width,
            'height' => $signature->default_height,
            'position' => $signature->default_position,
            'showBorder' => $signature->include_border,
            'showBackground' => ! empty($signature->background_color),
        ], $options);
        $this->showDate = $showDate && $signature->display_date;
        $this->customDate = $customDate;
    }

    /**
     * Get the formatted date for display.
     */
    public function getFormattedDate(): string
    {
        $date = $this->customDate ? \Carbon\Carbon::parse($this->customDate) : now();

        return $date->format($this->signature->date_format);
    }

    /**
     * Get signature image URL.
     */
    public function getSignatureImageUrl(): ?string
    {
        if (! $this->signature->hasSignatureImage()) {
            return null;
        }

        return $this->signature->signature_image_type === 'file'
            ? Storage::disk('public')->url($this->signature->signature_image_data)
            : 'data:image/png;base64,'.$this->signature->signature_image_data;
    }

    /**
     * Get stamp image URL.
     */
    public function getStampImageUrl(): ?string
    {
        if (! $this->signature->hasStampImage()) {
            return null;
        }

        return $this->signature->stamp_image_type === 'file'
            ? Storage::disk('public')->url($this->signature->stamp_image_data)
            : 'data:image/png;base64,'.$this->signature->stamp_image_data;
    }

    /**
     * Get CSS styles for the signature container.
     */
    public function getContainerStyles(): string
    {
        $styles = [
            'width: '.$this->options['width'].'px',
            'min-height: '.$this->options['height'].'px',
            'font-family: '.$this->signature->font_family,
            'color: '.$this->signature->text_color,
            'text-align: '.$this->options['position'],
            'position: relative',
            'display: flex',
            'flex-direction: column',
            'justify-content: center',
        ];

        if ($this->options['showBorder']) {
            $styles[] = 'border: 1px solid '.$this->signature->border_color;
            $styles[] = 'padding: 10px';
        }

        if ($this->options['showBackground'] && $this->signature->background_color) {
            $styles[] = 'background-color: '.$this->signature->background_color;
        }

        $alignment = match ($this->options['position']) {
            'left' => 'flex-start',
            'right' => 'flex-end',
            default => 'center'
        };
        $styles[] = 'align-items: '.$alignment;

        return implode('; ', $styles);
    }

    /**
     * Get font size in CSS format.
     */
    public function getFontSize(): string
    {
        return match ($this->signature->font_size) {
            'small' => '14px',
            'large' => '18px',
            default => '16px'
        };
    }

    /**
     * Get smaller font size for subtitles.
     */
    public function getSmallFontSize(): string
    {
        return match ($this->signature->font_size) {
            'small' => '12px',
            'large' => '16px',
            default => '14px'
        };
    }

    /**
     * Check if signature should show name.
     */
    public function shouldShowName(): bool
    {
        return $this->signature->display_name && ! empty($this->signature->full_name);
    }

    /**
     * Check if signature should show title.
     */
    public function shouldShowTitle(): bool
    {
        return $this->signature->display_title && ! empty($this->signature->position_title);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.signature-renderer');
    }
}
