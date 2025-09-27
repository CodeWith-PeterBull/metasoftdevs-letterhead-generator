<div class="container-fluid">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header with Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="mb-0">Document Signatures</h3>
            <small class="text-muted">Manage your digital signatures for documents</small>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" wire:click="createSignature" wire:loading.attr="disabled"
                wire:target="createSignature">
                <span wire:loading.remove wire:target="createSignature"><i class="fas fa-plus me-1"></i> New
                    Signature</span>
                <span wire:loading wire:target="createSignature"><i class="fas fa-spinner fa-spin me-1"></i>
                    Loading...</span>
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live="searchTerm" class="form-control"
                    placeholder="Search signatures by name, full name, or title...">
                @if ($searchTerm)
                    <button wire:click="$set('searchTerm', '')" class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Signatures Table -->
    <div class="card">
        <div class="card-body p-0">
            @if ($signatures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Images</th>
                                <th>Usage</th>
                                <th>Last Used</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signatures as $signature)
                                <tr wire:key="signature-{{ $signature->id }}">
                                    <td>
                                        <strong>{{ $signature->signature_name }}</strong>
                                        @if ($signature->description)
                                            <br><small
                                                class="text-muted">{{ Str::limit($signature->description, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $signature->full_name }}</td>
                                    <td>{{ $signature->position_title ?: '-' }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input wire:click="toggleActive({{ $signature->id }})"
                                                class="form-check-input" type="checkbox" role="switch"
                                                {{ $signature->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                <span
                                                    class="badge bg-{{ $signature->is_active ? 'success' : 'secondary' }}">
                                                    {{ $signature->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input wire:click="setAsDefault({{ $signature->id }})"
                                                class="form-check-input" type="radio"
                                                name="default_signature"
                                                {{ $signature->is_default ? 'checked' : '' }}
                                                wire:loading.attr="disabled"
                                                wire:target="setAsDefault({{ $signature->id }})">
                                            <label class="form-check-label d-flex align-items-center">
                                                @if ($signature->is_default)
                                                    <i class="fas fa-star text-warning me-1"></i>
                                                    <small class="text-warning">Default</small>
                                                @else
                                                    <small class="text-muted">Set Default</small>
                                                @endif
                                                <span wire:loading wire:target="setAsDefault({{ $signature->id }})" class="ms-1">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if ($signature->hasSignatureImage())
                                                <span class="badge bg-info">
                                                    <i class="fas fa-signature"></i>
                                                </span>
                                            @endif
                                            @if ($signature->hasStampImage())
                                                <span class="badge bg-success">
                                                    <i class="fas fa-stamp"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $signature->usage_count }}</td>
                                    <td>
                                        @if ($signature->last_used_at)
                                            <small>{{ $signature->last_used_at->format('M d, Y') }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-info"
                                                wire:click="viewSignature({{ $signature->id }})" title="View"
                                                wire:loading.attr="disabled"
                                                wire:target="viewSignature({{ $signature->id }})">
                                                <span wire:loading.remove
                                                    wire:target="viewSignature({{ $signature->id }})"><i
                                                        class="fas fa-eye"></i></span>
                                                <span wire:loading wire:target="viewSignature({{ $signature->id }})"><i
                                                        class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary"
                                                wire:click="editSignature({{ $signature->id }})" title="Edit"
                                                wire:loading.attr="disabled"
                                                wire:target="editSignature({{ $signature->id }})">
                                                <span wire:loading.remove
                                                    wire:target="editSignature({{ $signature->id }})"><i
                                                        class="fas fa-edit"></i></span>
                                                <span wire:loading wire:target="editSignature({{ $signature->id }})"><i
                                                        class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                wire:click="deleteSignature({{ $signature->id }})"
                                                wire:confirm="Are you sure you want to delete this signature?"
                                                title="Delete" wire:loading.attr="disabled"
                                                wire:target="deleteSignature({{ $signature->id }})">
                                                <span wire:loading.remove
                                                    wire:target="deleteSignature({{ $signature->id }})"><i
                                                        class="fas fa-trash"></i></span>
                                                <span wire:loading
                                                    wire:target="deleteSignature({{ $signature->id }})"><i
                                                        class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $signatures->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-signature fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No signatures found</h5>
                    <p class="text-muted">
                        @if ($searchTerm)
                            No signatures match your search criteria.
                        @else
                            Create your first digital signature to get started.
                        @endif
                    </p>
                    @if (!$searchTerm)
                        <button type="button" class="btn btn-primary" wire:click="createSignature">
                            <i class="fas fa-plus me-1"></i> Create First Signature
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Modal using the same pattern as InvoiceManagement -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @if ($modalMode === 'create')
                                Create New Signature
                            @elseif($modalMode === 'edit')
                                Edit Signature
                            @else
                                View Signature
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit.prevent="saveSignature">
                        <div class="modal-body">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-12 mb-3">
                                    <h6 class="text-primary border-bottom pb-2">Basic Information</h6>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signature Name *</label>
                                    <input type="text" wire:model="signatureForm.signature_name"
                                        class="form-control @error('signatureForm.signature_name') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.signature_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" wire:model="signatureForm.full_name"
                                        class="form-control @error('signatureForm.full_name') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position/Title</label>
                                    <input type="text" wire:model="signatureForm.position_title"
                                        class="form-control @error('signatureForm.position_title') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.position_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Initials</label>
                                    <input type="text" wire:model="signatureForm.initials"
                                        class="form-control @error('signatureForm.initials') is-invalid @enderror"
                                        maxlength="10" {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.initials')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="signatureForm.description"
                                        class="form-control @error('signatureForm.description') is-invalid @enderror" rows="2"
                                        {{ $modalMode === 'view' ? 'readonly' : '' }}></textarea>
                                    @error('signatureForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="col-md-12 mb-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model="signatureForm.is_default"
                                                    class="form-check-input" id="isDefault"
                                                    {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="isDefault">
                                                    Set as Default Signature
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model="signatureForm.is_active"
                                                    class="form-check-input" id="isActive"
                                                    {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="isActive">
                                                    Active Signature
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Images Section -->
                                <div class="col-md-12 mb-3">
                                    <h6 class="text-primary border-bottom pb-2">Images</h6>
                                </div>

                                @if ($modalMode === 'view')
                                    <!-- View Mode - Display Existing Images -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Signature Image</label>
                                        @if ($selectedSignature && $selectedSignature->hasSignatureImage())
                                            <div class="border rounded p-3 bg-light">
                                                @if ($selectedSignature->signature_image_type === 'file')
                                                    <img src="{{ Storage::disk('public')->url($selectedSignature->signature_image_data) }}"
                                                        alt="Current Signature Image"
                                                        class="img-fluid rounded shadow-sm"
                                                        style="max-height: 200px; object-fit: contain; display: block; margin: 0 auto;">
                                                @elseif ($selectedSignature->signature_image_type === 'base64')
                                                    <img src="data:image/png;base64,{{ $selectedSignature->signature_image_data }}"
                                                        alt="Current Signature Image"
                                                        class="img-fluid rounded shadow-sm"
                                                        style="max-height: 200px; object-fit: contain; display: block; margin: 0 auto;">
                                                @endif
                                                <div class="text-center mt-2">
                                                    <small class="text-muted">
                                                        Size: {{ $selectedSignature->signature_image_width }}x{{ $selectedSignature->signature_image_height }}px
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="border rounded p-3 bg-light text-center text-muted">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <p class="mb-0">No signature image available</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Stamp Image</label>
                                        @if ($selectedSignature && $selectedSignature->hasStampImage())
                                            <div class="border rounded p-3 bg-light">
                                                @if ($selectedSignature->stamp_image_type === 'file')
                                                    <img src="{{ Storage::disk('public')->url($selectedSignature->stamp_image_data) }}"
                                                        alt="Current Stamp Image"
                                                        class="img-fluid rounded shadow-sm"
                                                        style="max-height: 200px; object-fit: contain; display: block; margin: 0 auto;">
                                                @elseif ($selectedSignature->stamp_image_type === 'base64')
                                                    <img src="data:image/png;base64,{{ $selectedSignature->stamp_image_data }}"
                                                        alt="Current Stamp Image"
                                                        class="img-fluid rounded shadow-sm"
                                                        style="max-height: 200px; object-fit: contain; display: block; margin: 0 auto;">
                                                @endif
                                                <div class="text-center mt-2">
                                                    <small class="text-muted">
                                                        Size: {{ $selectedSignature->stamp_image_width }}x{{ $selectedSignature->stamp_image_height }}px
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="border rounded p-3 bg-light text-center text-muted">
                                                <i class="fas fa-stamp fa-2x mb-2"></i>
                                                <p class="mb-0">No stamp image available</p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <!-- Create/Edit Mode - File Upload Fields -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Signature Image</label>
                                        <input type="file" wire:model="signatureImage"
                                            class="form-control @error('signatureImage') is-invalid @enderror"
                                            accept="image/*">
                                        @error('signatureImage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Show new upload preview -->
                                        @if ($signatureImage)
                                            <div class="mt-2">
                                                <img src="{{ $signatureImage->temporaryUrl() }}" alt="New Signature Preview"
                                                    class="img-fluid rounded shadow-sm"
                                                    style="max-height: 200px; object-fit: contain; display: block;">
                                                <div wire:loading wire:target="signatureImage" class="text-info mt-1">
                                                    <i class="fas fa-spinner fa-spin"></i> Uploading...
                                                </div>
                                            </div>
                                        @elseif($modalMode === 'edit' && $selectedSignature && $selectedSignature->hasSignatureImage())
                                            <!-- Show current image in edit mode if no new upload -->
                                            <div class="mt-2">
                                                <small class="text-muted">Current image:</small>
                                                @if ($selectedSignature->signature_image_type === 'file')
                                                    <img src="{{ Storage::disk('public')->url($selectedSignature->signature_image_data) }}"
                                                        alt="Current Signature"
                                                        class="img-fluid rounded shadow-sm d-block mt-1"
                                                        style="max-height: 150px; object-fit: contain;">
                                                @elseif ($selectedSignature->signature_image_type === 'base64')
                                                    <img src="data:image/png;base64,{{ $selectedSignature->signature_image_data }}"
                                                        alt="Current Signature"
                                                        class="img-fluid rounded shadow-sm d-block mt-1"
                                                        style="max-height: 150px; object-fit: contain;">
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Stamp Image</label>
                                        <input type="file" wire:model="stampImage"
                                            class="form-control @error('stampImage') is-invalid @enderror"
                                            accept="image/*">
                                        @error('stampImage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Show new upload preview -->
                                        @if ($stampImage)
                                            <div class="mt-2">
                                                <img src="{{ $stampImage->temporaryUrl() }}" alt="New Stamp Preview"
                                                    class="img-fluid rounded shadow-sm"
                                                    style="max-height: 200px; object-fit: contain; display: block;">
                                                <div wire:loading wire:target="stampImage" class="text-info mt-1">
                                                    <i class="fas fa-spinner fa-spin"></i> Uploading...
                                                </div>
                                            </div>
                                        @elseif($modalMode === 'edit' && $selectedSignature && $selectedSignature->hasStampImage())
                                            <!-- Show current image in edit mode if no new upload -->
                                            <div class="mt-2">
                                                <small class="text-muted">Current image:</small>
                                                @if ($selectedSignature->stamp_image_type === 'file')
                                                    <img src="{{ Storage::disk('public')->url($selectedSignature->stamp_image_data) }}"
                                                        alt="Current Stamp"
                                                        class="img-fluid rounded shadow-sm d-block mt-1"
                                                        style="max-height: 150px; object-fit: contain;">
                                                @elseif ($selectedSignature->stamp_image_type === 'base64')
                                                    <img src="data:image/png;base64,{{ $selectedSignature->stamp_image_data }}"
                                                        alt="Current Stamp"
                                                        class="img-fluid rounded shadow-sm d-block mt-1"
                                                        style="max-height: 150px; object-fit: contain;">
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Signature Preview (View Mode Only) -->                                @if ($modalMode === 'view' && $selectedSignature)                                    <div class="col-md-12 mb-4">                                        <h6 class="text-primary border-bottom pb-2">Signature Preview</h6>                                        <div class="card border-2 border-primary shadow-sm">                                            <div class="card-header bg-primary bg-opacity-10 text-center">                                                <small class="text-muted fw-semibold">                                                    <i class="fas fa-eye me-1"></i>Document Preview                                                </small>                                            </div>                                            <div class="card-body p-4 bg-light">                                                <div class="d-flex justify-content-center">                                                    <div class="border border-2 border-secondary rounded p-3 bg-white shadow-sm" style="min-width: 300px;">                                                        <x-signature-preview                                                            :signature="$selectedSignature"                                                            size="large"                                                            :show-name="true"                                                            :show-title="true"                                                            :interactive="false" />                                                    </div>                                                </div>                                            </div>                                            <div class="card-footer text-center bg-light">                                                <small class="text-muted">                                                    <i class="fas fa-info-circle me-1"></i>                                                    This is how your signature will appear in documents                                                </small>                                            </div>                                        </div>                                    </div>                                @endif

                                <!-- Display Options -->
                                <div class="col-md-12 mb-3">
                                    <h6 class="text-primary border-bottom pb-2">Display Options</h6>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="signatureForm.display_name"
                                            class="form-check-input" id="displayName"
                                            {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="displayName">
                                            Display Name
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="signatureForm.display_title"
                                            class="form-check-input" id="displayTitle"
                                            {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="displayTitle">
                                            Display Title
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="signatureForm.display_date"
                                            class="form-check-input" id="displayDate"
                                            {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="displayDate">
                                            Display Date
                                        </label>
                                    </div>
                                </div>

                                <!-- Styling -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Font Family</label>
                                    <select wire:model="signatureForm.font_family"
                                        class="form-select @error('signatureForm.font_family') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <option value="Arial">Arial</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Helvetica">Helvetica</option>
                                        <option value="Georgia">Georgia</option>
                                    </select>
                                    @error('signatureForm.font_family')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Font Size</label>
                                    <select wire:model="signatureForm.font_size"
                                        class="form-select @error('signatureForm.font_size') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <option value="small">Small</option>
                                        <option value="medium">Medium</option>
                                        <option value="large">Large</option>
                                    </select>
                                    @error('signatureForm.font_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Layout -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Position</label>
                                    <select wire:model="signatureForm.default_position"
                                        class="form-select @error('signatureForm.default_position') is-invalid @enderror"
                                        {{ $modalMode === 'view' ? 'disabled' : '' }}>
                                        <option value="left">Left</option>
                                        <option value="center">Center</option>
                                        <option value="right">Right</option>
                                    </select>
                                    @error('signatureForm.default_position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Width (px)</label>
                                    <input type="number" wire:model="signatureForm.default_width"
                                        class="form-control @error('signatureForm.default_width') is-invalid @enderror"
                                        min="100" max="500" {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.default_width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Height (px)</label>
                                    <input type="number" wire:model="signatureForm.default_height"
                                        class="form-control @error('signatureForm.default_height') is-invalid @enderror"
                                        min="50" max="300" {{ $modalMode === 'view' ? 'readonly' : '' }}>
                                    @error('signatureForm.default_height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal"
                                wire:loading.attr="disabled">
                                <i class="fas fa-times me-1"></i>{{ $modalMode === 'view' ? 'Close' : 'Cancel' }}
                            </button>
                            @if ($modalMode !== 'view')
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                    wire:target="saveSignature">
                                    <span wire:loading.remove wire:target="saveSignature">
                                        <i
                                            class="fas fa-{{ $modalMode === 'create' ? 'plus' : 'save' }} me-1"></i>{{ $modalMode === 'create' ? 'Create Signature' : 'Update Signature' }}
                                    </span>
                                    <span wire:loading wire:target="saveSignature">
                                        <i
                                            class="fas fa-spinner fa-spin me-1"></i>{{ $modalMode === 'create' ? 'Creating...' : 'Updating...' }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
