<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 mb-1 text-dark">Company Management</h1>
                <p class="text-muted mb-0">Manage your company profiles for letterhead generation</p>
            </div>
            <div class="col-md-4 text-end">
                <button wire:click="openCreateModal" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>
                    Add New Company
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Controls -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input wire:model.live="search" type="text" placeholder="Search companies..."
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-nowrap">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Companies Grid -->
    <div class="row g-4 mb-4">
        @forelse ($companies as $company)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $company->is_default ? 'border-primary' : '' }}">
                    <!-- Company Header -->
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h5 class="card-title mb-0">{{ $company->name }}</h5>
                                    @if ($company->is_default)
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="fas fa-star me-1"></i>Default
                                        </span>
                                    @endif
                                </div>
                                @if ($company->industry)
                                    <p class="text-muted small mb-0">{{ $company->industry }}</p>
                                @endif
                            </div>

                            <!-- Logo -->
                            @if ($company->has_logo)
                                <div class="flex-shrink-0">
                                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }} Logo" class="rounded"
                                        style="width: 48px; height: 48px; object-fit: contain; background-color: #f8f9fa; padding: 2px;">
                                </div>
                            @else
                                <div class="flex-shrink-0 bg-light rounded d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-building text-muted"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Company Details -->
                    <div class="card-body">
                        <div class="mb-3">
                            @if ($company->address)
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <i class="fas fa-map-marker-alt text-muted mt-1"></i>
                                    <span class="text-muted small">{{ Str::limit($company->address, 50) }}</span>
                                </div>
                            @endif

                            @if ($company->phone_1)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-phone text-muted"></i>
                                    <span class="text-muted small">{{ $company->phone_1 }}</span>
                                </div>
                            @endif

                            @if ($company->email_1)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-envelope text-muted"></i>
                                    <span class="text-muted small">{{ $company->email_1 }}</span>
                                </div>
                            @endif

                            @if ($company->website)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-globe text-muted"></i>
                                    <a href="{{ $company->website }}" target="_blank"
                                        class="text-primary text-decoration-none small">{{ parse_url($company->website, PHP_URL_HOST) }}</a>
                                </div>
                            @endif
                        </div>

                        <!-- Status and Template Info -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $company->is_active ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas {{ $company->is_active ? 'fa-check' : 'fa-times' }} me-1"></i>
                                {{ $company->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-file-alt me-1"></i>
                                {{ $templateOptions[$company->default_template] }}
                            </span>
                            <span class="badge bg-info">
                                <i class="fas fa-file me-1"></i>
                                {{ $paperSizeOptions[$company->default_paper_size] }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('letterhead.form', $company) }}"
                                   class="btn btn-primary btn-sm"
                                   title="Generate letterhead using this company's information">
                                    <i class="fas fa-file-word me-1"></i>Generate Letterhead
                                </a>
                                
                                <button wire:click="openEditModal({{ $company->id }})"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </button>

                                @if (!$company->is_default)
                                    <button wire:click="setAsDefault({{ $company->id }})"
                                        class="btn btn-outline-success btn-sm"
                                        {{ !$company->is_active ? 'disabled' : '' }}>
                                        <i class="fas fa-star me-1"></i>Set Default
                                    </button>
                                @endif
                            </div>

                            <div class="btn-group" role="group">
                                <button wire:click="duplicateCompany({{ $company->id }})"
                                    class="btn btn-outline-secondary btn-sm d-flex align-items-center"
                                    title="Create a copy of this company">
                                    <i class="fas fa-copy me-1"></i>
                                    <small>Duplicate</small>
                                </button>

                                <button wire:click="toggleActive({{ $company->id }})"
                                    class="btn btn-outline-{{ $company->is_active ? 'warning' : 'success' }} btn-sm d-flex align-items-center"
                                    title="{{ $company->is_active ? 'Deactivate this company' : 'Activate this company' }}">
                                    <i class="fas {{ $company->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                                    <small>{{ $company->is_active ? 'Deactivate' : 'Activate' }}</small>
                                </button>

                                <button wire:click="confirmDelete({{ $company->id }})"
                                    class="btn btn-outline-danger btn-sm d-flex align-items-center"
                                    title="Delete this company permanently">
                                    <i class="fas fa-trash me-1"></i>
                                    <small>Delete</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-building fa-4x text-muted"></i>
                    </div>
                    <h3 class="h5 text-muted">No companies found</h3>
                    <p class="text-muted mb-4">Get started by creating a new company profile.</p>
                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add New Company
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($companies->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $companies->links() }}
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $modalTitle }}</h5>
                        <button wire:click="closeModal" type="button" class="btn-close"></button>
                    </div>

                    <!-- Modal Content -->
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <!-- Basic Information -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">Basic Information</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="name" class="form-label">Company Name <span
                                                class="text-danger">*</span></label>
                                        <input wire:model="name" type="text" id="name" class="form-control">
                                        @error('name')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="address" class="form-label">Address <span
                                                class="text-danger">*</span></label>
                                        <textarea wire:model="address" id="address" rows="3" class="form-control"></textarea>
                                        @error('address')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone_1" class="form-label">Primary Phone</label>
                                        <input wire:model="phone_1" type="tel" id="phone_1"
                                            class="form-control">
                                        @error('phone_1')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone_2" class="form-label">Secondary Phone</label>
                                        <input wire:model="phone_2" type="tel" id="phone_2"
                                            class="form-control">
                                        @error('phone_2')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email_1" class="form-label">Primary Email</label>
                                        <input wire:model="email_1" type="email" id="email_1"
                                            class="form-control">
                                        @error('email_1')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email_2" class="form-label">Secondary Email</label>
                                        <input wire:model="email_2" type="email" id="email_2"
                                            class="form-control">
                                        @error('email_2')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="website" class="form-label">Website</label>
                                        <input wire:model="website" type="url" id="website"
                                            class="form-control">
                                        @error('website')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-primary mb-3">Social Media</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                        <input wire:model="linkedin_url" type="url" id="linkedin_url"
                                            class="form-control">
                                        @error('linkedin_url')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="twitter_handle" class="form-label">Twitter Handle</label>
                                        <input wire:model="twitter_handle" type="text" id="twitter_handle"
                                            placeholder="@username" class="form-control">
                                        @error('twitter_handle')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="facebook_url" class="form-label">Facebook URL</label>
                                        <input wire:model="facebook_url" type="url" id="facebook_url"
                                            class="form-control">
                                        @error('facebook_url')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Branding -->
                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-primary mb-3">Branding</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="logo" class="form-label">Company Logo</label>
                                        <input wire:model="logo" type="file" id="logo" accept="image/*"
                                            class="form-control">
                                        @error('logo')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror

                                        @if ($logo)
                                            <div class="mt-2">
                                                <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview"
                                                    class="rounded"
                                                    style="width: 64px; height: 64px; object-fit: contain; background-color: #f8f9fa; padding: 2px;">
                                            </div>
                                        @elseif ($isEditing && $editingCompany && $editingCompany->has_logo)
                                            <div class="mt-2">
                                                <img src="{{ $editingCompany->logo_url }}" alt="Current logo"
                                                    class="rounded"
                                                    style="width: 64px; height: 64px; object-fit: contain; background-color: #f8f9fa; padding: 2px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <label for="primary_color" class="form-label">Primary Color <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input wire:model="primary_color" type="color" id="primary_color"
                                                class="form-control form-control-color">
                                            <input wire:model="primary_color" type="text" placeholder="#000000"
                                                class="form-control">
                                        </div>
                                        @error('primary_color')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-primary mb-3">Business Information</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="industry" class="form-label">Industry</label>
                                        <input wire:model="industry" type="text" id="industry"
                                            class="form-control">
                                        @error('industry')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea wire:model="description" id="description" rows="3" class="form-control"></textarea>
                                        @error('description')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Letterhead Preferences -->
                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-primary mb-3">Letterhead Preferences</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="default_template" class="form-label">Default Template <span
                                                class="text-danger">*</span></label>
                                        <select wire:model="default_template" id="default_template"
                                            class="form-select">
                                            @foreach ($templateOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('default_template')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="default_paper_size" class="form-label">Default Paper Size <span
                                                class="text-danger">*</span></label>
                                        <select wire:model="default_paper_size" id="default_paper_size"
                                            class="form-select">
                                            @foreach ($paperSizeOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('default_paper_size')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="form-check">
                                        <input wire:model="include_social_media" type="checkbox"
                                            class="form-check-input" id="include_social_media">
                                        <label class="form-check-label" for="include_social_media">
                                            Include social media in letterheads
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input wire:model="include_registration_details" type="checkbox"
                                            class="form-check-input" id="include_registration_details">
                                        <label class="form-check-label" for="include_registration_details">
                                            Include registration details
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Settings -->
                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-primary mb-3">Status Settings</h6>
                                <div class="form-check">
                                    <input wire:model="is_active" type="checkbox" class="form-check-input"
                                        id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Company is active
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input wire:model="is_default" type="checkbox" class="form-check-input"
                                        id="is_default">
                                    <label class="form-check-label" for="is_default">
                                        Set as default company
                                    </label>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ $isEditing ? 'Update Company' : 'Create Company' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($confirmingDeletion)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div class="w-100 text-center">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body text-center">
                        <h5 class="modal-title mb-3">Delete Company</h5>
                        <p class="text-muted">
                            Are you sure you want to delete "<strong>{{ $companyToDelete?->name }}</strong>"? This
                            action cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <button wire:click="delete" class="btn btn-danger me-2">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                        <button wire:click="cancelDelete" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.flex class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="background-color: rgba(0,0,0,0.5); z-index: 9999; display: none;">
        <div class="card">
            <div class="card-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Processing...</div>
            </div>
        </div>
    </div>
</div>
