{{-- Signature Preview Component
     Provides a compact preview of signatures
     Usage: <x-signature-preview :signature="$signature" size="medium" />
--}}

<div class="{{ $getContainerClass() }}"
     data-signature-id="{{ $signature->id }}"
     style="font-size: {{ $getFontSize() }}; line-height: 1.3;">

    @if($signature->hasSignatureImage())
        <div class="signature-preview-image mb-1">
            @php $dimensions = $getImageDimensions(); @endphp
            @if($signature->signature_image_type === 'file')
                <img src="{{ Storage::disk('public')->url($signature->signature_image_data) }}"
                     alt="Signature"
                     style="max-width: {{ $dimensions['width'] }}; height: auto; max-height: {{ $dimensions['height'] }}; object-fit: contain; display: block;">
            @else
                <img src="data:image/png;base64,{{ $signature->signature_image_data }}"
                     alt="Signature"
                     style="max-width: {{ $dimensions['width'] }}; height: auto; max-height: {{ $dimensions['height'] }}; object-fit: contain; display: block;">
            @endif
        </div>
    @endif

    @if($showName && $signature->display_name && $signature->full_name)
        <div class="signature-preview-name fw-bold">
            @if(!empty($signature->initials))
                {{ $signature->initials }} {{ $signature->full_name }}
            @else
                {{ $signature->full_name }}
            @endif
        </div>
    @endif

    @if($showTitle && $signature->display_title && $signature->position_title)
        <div class="signature-preview-title text-muted">
            {{ $signature->position_title }}
        </div>
    @endif

    @if($signature->display_date)
        <div class="signature-preview-date text-muted mt-1" style="font-size: {{ (int) str_replace('px', '', $getFontSize()) - 1 }}px;">
            {{ now()->format($signature->date_format) }}
        </div>
    @endif

    @if($signature->hasStampImage() && $size !== 'small')
        <div class="signature-preview-stamp mt-1">
            @if($signature->stamp_image_type === 'file')
                <img src="{{ Storage::disk('public')->url($signature->stamp_image_data) }}"
                     alt="Stamp"
                     style="width: 30px; height: 30px; object-fit: contain;">
            @else
                <img src="data:image/png;base64,{{ $signature->stamp_image_data }}"
                     alt="Stamp"
                     style="width: 30px; height: 30px; object-fit: contain;">
            @endif
        </div>
    @endif
</div>

{{-- Component Styles --}}
@push('styles')
<style>
    .signature-preview {
        display: inline-block;
        padding: 0.5rem;
        border-radius: 4px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        text-align: center;
        transition: all 0.2s ease;
        min-width: 100px;
    }

    .signature-preview-sm {
        padding: 0.25rem;
        min-width: 80px;
        border-radius: 3px;
    }

    .signature-preview-md {
        padding: 0.5rem;
        min-width: 120px;
    }

    .signature-preview-lg {
        padding: 0.75rem;
        min-width: 160px;
        border-radius: 6px;
    }

    .signature-preview-interactive {
        cursor: pointer;
    }

    .signature-preview-interactive:hover {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.05);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .signature-preview img {
        margin: 0 auto;
    }

    .signature-preview-name {
        margin-bottom: 0.25rem;
    }

    .signature-preview-title {
        margin-bottom: 0.25rem;
        font-size: 90%;
    }

    .signature-preview-date {
        font-size: 85%;
        opacity: 0.8;
    }

    .signature-preview-stamp {
        text-align: center;
    }
</style>
@endpush