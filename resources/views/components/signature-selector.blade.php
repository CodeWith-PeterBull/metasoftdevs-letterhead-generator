{{-- Signature Selector Component
     Provides UI for selecting signatures in documents
     Usage: <x-signature-selector input-name="signature_id" layout="grid" />
--}}

<div class="signature-selector {{ $getLayoutClass() }}" data-input-name="{{ $inputName }}">

    @if($layout === 'dropdown')
        {{-- Dropdown Layout --}}
        <div class="form-group">
            <label for="{{ $inputName }}" class="form-label">Select Signature:</label>
            <select name="{{ $inputName }}"
                    id="{{ $inputName }}"
                    class="form-select signature-dropdown">

                @if($allowNone)
                    <option value="" {{ !$selectedId ? 'selected' : '' }}>
                        No Signature
                    </option>
                @endif

                @foreach($signatures as $signature)
                    <option value="{{ $signature->id }}"
                            {{ $isSelected($signature) ? 'selected' : '' }}
                            data-signature-id="{{ $signature->id }}">
                        {{ $signature->signature_name }}
                        @if($signature->is_default) (Default) @endif
                        - {{ $signature->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($showPreview && $getSelectedSignature())
            <div class="signature-preview-container mt-3">
                <h6>Preview:</h6>
                <x-signature-renderer
                    :signature="$getSelectedSignature()"
                    render-style="preview" />
            </div>
        @endif

    @elseif($layout === 'list')
        {{-- List Layout --}}
        <div class="signature-list">
            <input type="hidden" name="{{ $inputName }}" value="{{ $selectedId }}" class="signature-input">

            @if($allowNone)
                <div class="signature-option signature-none {{ !$selectedId ? 'selected' : '' }}"
                     data-signature-id="">
                    <div class="d-flex align-items-center">
                        <input type="radio"
                               name="{{ $inputName }}_radio"
                               value=""
                               id="signature_none"
                               {{ !$selectedId ? 'checked' : '' }}
                               class="form-check-input me-3">
                        <label for="signature_none" class="form-check-label">
                            <strong>No Signature</strong>
                        </label>
                    </div>
                </div>
            @endif

            @foreach($signatures as $signature)
                <div class="signature-option {{ $isSelected($signature) ? 'selected' : '' }}"
                     data-signature-id="{{ $signature->id }}">
                    <div class="d-flex align-items-start">
                        <input type="radio"
                               name="{{ $inputName }}_radio"
                               value="{{ $signature->id }}"
                               id="signature_{{ $signature->id }}"
                               {{ $isSelected($signature) ? 'checked' : '' }}
                               class="form-check-input me-3 mt-1">

                        <div class="flex-grow-1">
                            <label for="signature_{{ $signature->id }}" class="form-check-label w-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $signature->signature_name }}</strong>
                                        @if($signature->is_default)
                                            <span class="badge bg-primary ms-2">Default</span>
                                        @endif
                                        <div class="text-muted small">{{ $signature->full_name }}</div>
                                        @if($signature->position_title)
                                            <div class="text-muted small">{{ $signature->position_title }}</div>
                                        @endif
                                    </div>

                                    @if($showPreview)
                                        <div class="signature-mini-preview">
                                            <x-signature-renderer
                                                :signature="$signature"
                                                render-style="preview" />
                                        </div>
                                    @endif
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- Grid Layout (Default) --}}
        <div class="signature-grid">
            <input type="hidden" name="{{ $inputName }}" value="{{ $selectedId }}" class="signature-input">

            @if($allowNone)
                <div class="signature-card signature-none {{ !$selectedId ? 'selected' : '' }}"
                     data-signature-id="">
                    <input type="radio"
                           name="{{ $inputName }}_radio"
                           value=""
                           id="signature_none_grid"
                           {{ !$selectedId ? 'checked' : '' }}
                           class="signature-radio d-none">
                    <label for="signature_none_grid" class="signature-card-label">
                        <div class="text-center p-4">
                            <i class="fas fa-times-circle fa-2x text-muted mb-2"></i>
                            <div><strong>No Signature</strong></div>
                        </div>
                    </label>
                </div>
            @endif

            @foreach($signatures as $signature)
                <div class="signature-card {{ $isSelected($signature) ? 'selected' : '' }}"
                     data-signature-id="{{ $signature->id }}">
                    <input type="radio"
                           name="{{ $inputName }}_radio"
                           value="{{ $signature->id }}"
                           id="signature_grid_{{ $signature->id }}"
                           {{ $isSelected($signature) ? 'checked' : '' }}
                           class="signature-radio d-none">

                    <label for="signature_grid_{{ $signature->id }}" class="signature-card-label">
                        <div class="signature-card-header">
                            <div class="signature-card-title">
                                {{ $signature->signature_name }}
                                @if($signature->is_default)
                                    <span class="badge bg-primary ms-1">Default</span>
                                @endif
                            </div>
                            <div class="signature-card-subtitle">{{ $signature->full_name }}</div>
                        </div>

                        @if($showPreview)
                            <div class="signature-card-preview">
                                <x-signature-renderer
                                    :signature="$signature"
                                    render-style="preview" />
                            </div>
                        @endif
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    @if($signatures->isEmpty())
        <div class="no-signatures-message text-center py-4">
            <i class="fas fa-signature fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Signatures Available</h5>
            <p class="text-muted">Please create a signature first in the signature management section.</p>
            <a href="{{ route('signatures.signatures-management') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Signature
            </a>
        </div>
    @endif
</div>

{{-- Component Styles --}}
@push('styles')
<style>
    .signature-selector {
        width: 100%;
    }

    /* Grid Layout */
    .signature-selector-grid .signature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .signature-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        background: #fff;
    }

    .signature-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }

    .signature-card.selected {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.05);
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }

    .signature-card-label {
        display: block;
        padding: 1rem;
        margin: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .signature-card-header {
        margin-bottom: 0.5rem;
    }

    .signature-card-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .signature-card-subtitle {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .signature-card-preview {
        border-top: 1px solid #e9ecef;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }

    /* List Layout */
    .signature-selector-list .signature-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .signature-option {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .signature-option:hover {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.02);
    }

    .signature-option.selected {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.05);
    }

    .signature-mini-preview {
        flex-shrink: 0;
        margin-left: 1rem;
        max-width: 200px;
    }

    /* Dropdown Layout */
    .signature-selector-dropdown .signature-preview-container {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        background-color: #f8f9fa;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .signature-selector-grid .signature-grid {
            grid-template-columns: 1fr;
        }

        .signature-mini-preview {
            display: none;
        }
    }
</style>
@endpush

{{-- Component JavaScript --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle signature selection
    document.querySelectorAll('.signature-selector').forEach(function(selector) {
        const hiddenInput = selector.querySelector('.signature-input');
        const radioInputs = selector.querySelectorAll('.signature-radio');
        const cards = selector.querySelectorAll('.signature-card, .signature-option');

        // Update hidden input when radio changes
        radioInputs.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (hiddenInput) {
                    hiddenInput.value = this.value;
                }

                // Update visual selection
                cards.forEach(function(card) {
                    card.classList.remove('selected');
                });

                if (this.value) {
                    const selectedCard = selector.querySelector(`[data-signature-id="${this.value}"]`);
                    if (selectedCard) {
                        selectedCard.classList.add('selected');
                    }
                } else {
                    const noneCard = selector.querySelector('.signature-none');
                    if (noneCard) {
                        noneCard.classList.add('selected');
                    }
                }

                // Trigger custom event
                selector.dispatchEvent(new CustomEvent('signature-changed', {
                    detail: { signatureId: this.value }
                }));
            });
        });

        // Handle dropdown selection for preview update
        const dropdown = selector.querySelector('.signature-dropdown');
        if (dropdown) {
            dropdown.addEventListener('change', function() {
                // Logic to update preview if needed
                selector.dispatchEvent(new CustomEvent('signature-changed', {
                    detail: { signatureId: this.value }
                }));
            });
        }
    });
});
</script>
@endpush