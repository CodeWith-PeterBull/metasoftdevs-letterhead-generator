{{-- Signature Renderer Component
     Renders a complete digital signature with all components
     Usage: <x-signature-renderer :signature="$signature" render-style="full" />
--}}

<div class="signature-renderer signature-style-{{ $renderStyle }}"
     style="{{ $getContainerStyles() }}"
     data-signature-id="{{ $signature->id }}">

    @if($renderStyle === 'full' || $renderStyle === 'document')
        {{-- Signature Image (if available) --}}
        @if($getSignatureImageUrl())
            <div class="signature-image mb-2">
                <img src="{{ $getSignatureImageUrl() }}"
                     alt="Signature of {{ $signature->full_name }}"
                     style="max-width: 100%; height: auto; max-height: 60px; object-fit: contain;">
            </div>
        @endif

        {{-- Full Name --}}
        @if($shouldShowName())
            <div class="signature-name fw-bold"
                 style="font-size: {{ $getFontSize() }}; margin-bottom: 2px;">
                {{ $signature->full_name }}
            </div>
        @endif

        {{-- Position Title --}}
        @if($shouldShowTitle())
            <div class="signature-title"
                 style="font-size: {{ $getSmallFontSize() }}; margin-bottom: 4px;">
                {{ $signature->position_title }}
            </div>
        @endif

        {{-- Footer with Stamp and Date --}}
        @if($getStampImageUrl() || $showDate)
            <div class="signature-footer d-flex justify-content-between align-items-center mt-2"
                 style="min-height: 40px;">

                {{-- Stamp Image --}}
                @if($getStampImageUrl())
                    <div class="signature-stamp">
                        <img src="{{ $getStampImageUrl() }}"
                             alt="Official Stamp"
                             style="width: 40px; height: 40px; object-fit: contain;">
                    </div>
                @endif

                {{-- Date --}}
                @if($showDate)
                    <div class="signature-date text-end"
                         style="font-size: {{ $getSmallFontSize() }};">
                        {{ $getFormattedDate() }}
                    </div>
                @endif
            </div>
        @endif

    @elseif($renderStyle === 'compact')
        {{-- Compact Style - Name and Title only --}}
        <div class="signature-compact">
            @if($shouldShowName())
                <div class="signature-name fw-bold" style="font-size: 14px;">
                    {{ $signature->full_name }}
                </div>
            @endif
            @if($shouldShowTitle())
                <div class="signature-title" style="font-size: 12px; color: #666;">
                    {{ $signature->position_title }}
                </div>
            @endif
        </div>

    @elseif($renderStyle === 'image-only')
        {{-- Image Only Style --}}
        @if($getSignatureImageUrl())
            <div class="signature-image-only">
                <img src="{{ $getSignatureImageUrl() }}"
                     alt="Signature"
                     style="max-width: {{ $options['width'] }}px; height: auto; max-height: {{ $options['height'] }}px; object-fit: contain;">
            </div>
        @endif

    @elseif($renderStyle === 'minimal')
        {{-- Minimal Style - Name and Date --}}
        <div class="signature-minimal" style="font-size: 14px;">
            @if($shouldShowName())
                <span class="fw-bold">{{ $signature->full_name }}</span>
            @endif
            @if($showDate && $shouldShowName())
                <span class="text-muted"> • {{ $getFormattedDate() }}</span>
            @elseif($showDate)
                <span>{{ $getFormattedDate() }}</span>
            @endif
        </div>

    @elseif($renderStyle === 'preview')
        {{-- Preview Style - All elements in small format --}}
        <div class="signature-preview" style="font-size: 11px; line-height: 1.3;">
            @if($getSignatureImageUrl())
                <div class="mb-1">
                    <img src="{{ $getSignatureImageUrl() }}"
                         alt="Signature"
                         style="max-width: 80px; height: auto; max-height: 30px; object-fit: contain;">
                </div>
            @endif
            @if($shouldShowName())
                <div class="fw-bold">{{ $signature->full_name }}</div>
            @endif
            @if($shouldShowTitle())
                <div style="font-size: 10px; color: #666;">{{ $signature->position_title }}</div>
            @endif
            @if($showDate)
                <div style="font-size: 10px; color: #999;">{{ $getFormattedDate() }}</div>
            @endif
        </div>
    @endif

    {{-- Record usage when rendered (optional) --}}
    @if($renderStyle === 'document')
        @php
            // Only record usage for document rendering to avoid excessive logging
            $signature->recordUsage('document-render');
        @endphp
    @endif
</div>

{{-- Optional CSS for better styling --}}
@push('styles')
<style>
    .signature-renderer {
        font-family: inherit;
        line-height: 1.4;
    }

    .signature-renderer img {
        display: block;
    }

    .signature-style-full {
        border-radius: 4px;
    }

    .signature-style-compact {
        padding: 8px;
    }

    .signature-style-minimal {
        padding: 4px 0;
    }

    .signature-style-preview {
        padding: 6px;
        background-color: #f8f9fa;
        border-radius: 3px;
    }

    @media print {
        .signature-renderer {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endpush