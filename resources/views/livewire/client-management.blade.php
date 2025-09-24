<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Client Management</h2>
        <button type="button" class="btn btn-primary" wire:click="createClient" wire:loading.attr="disabled" wire:target="createClient">
            <span wire:loading.remove wire:target="createClient"><i class="fas fa-plus-circle me-1"></i> Create Client</span>
            <span wire:loading wire:target="createClient"><i class="fas fa-spinner fa-spin me-1"></i> Loading...</span>
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search clients..."
                               wire:model.live="searchTerm">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($clients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Company Name</th>
                                <th>Contact Info</th>
                                <th>Payment Methods</th>
                                <th>Invoices</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr wire:key="client-{{ $client->id }}">
                                    <td>
                                        <div class="fw-bold">{{ $client->company_name }}</div>
                                        @if($client->company_address)
                                            <small class="text-muted">{{ Str::limit($client->company_address, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            @if($client->primary_phone)
                                                <div><i class="fas fa-phone me-1 text-muted"></i> {{ $client->primary_phone }}</div>
                                            @endif
                                            @if($client->email)
                                                <div><i class="fas fa-envelope me-1 text-muted"></i> {{ $client->email }}</div>
                                            @endif
                                            @if($client->website)
                                                <div><i class="fas fa-globe me-1 text-muted"></i>
                                                    <a href="{{ $client->website }}" target="_blank" class="text-decoration-none">
                                                        {{ Str::limit($client->website, 25) }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($client->mpesa_account || $client->bank_account)
                                            @if($client->mpesa_account)
                                                <div><span class="badge bg-success me-1">MPESA</span> {{ $client->mpesa_account }}</div>
                                            @endif
                                            @if($client->bank_account)
                                                <div><span class="badge bg-info me-1">Bank</span> {{ $client->bank_name ?: 'Account' }}: {{ $client->bank_account }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">No payment methods</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $client->invoices_count ?? $client->invoices()->count() }}</span>
                                        <small class="text-muted">invoices</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    wire:click="viewClient({{ $client->id }})"
                                                    title="View Client"
                                                    wire:loading.attr="disabled"
                                                    wire:target="viewClient({{ $client->id }})">
                                                <span wire:loading.remove wire:target="viewClient({{ $client->id }})"><i class="fas fa-eye"></i></span>
                                                <span wire:loading wire:target="viewClient({{ $client->id }})"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>

                                            <button type="button" class="btn btn-outline-secondary"
                                                    wire:click="editClient({{ $client->id }})"
                                                    title="Edit Client"
                                                    wire:loading.attr="disabled"
                                                    wire:target="editClient({{ $client->id }})">
                                                <span wire:loading.remove wire:target="editClient({{ $client->id }})"><i class="fas fa-edit"></i></span>
                                                <span wire:loading wire:target="editClient({{ $client->id }})"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>

                                            <button type="button" class="btn btn-outline-danger"
                                                    wire:click="deleteClient({{ $client->id }})"
                                                    wire:confirm="Are you sure you want to delete client '{{ $client->company_name }}'? This action cannot be undone."
                                                    title="Delete Client"
                                                    wire:loading.attr="disabled"
                                                    wire:target="deleteClient({{ $client->id }})">
                                                <span wire:loading.remove wire:target="deleteClient({{ $client->id }})"><i class="fas fa-trash"></i></span>
                                                <span wire:loading wire:target="deleteClient({{ $client->id }})"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No clients found</h5>
                    <p class="text-muted mb-4">Get started by creating your first client.</p>
                    <button type="button" class="btn btn-primary" wire:click="createClient" wire:loading.attr="disabled" wire:target="createClient">
                        <span wire:loading.remove wire:target="createClient"><i class="fas fa-plus-circle me-1"></i> Create Client</span>
                        <span wire:loading wire:target="createClient"><i class="fas fa-spinner fa-spin me-1"></i> Loading...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @if($modalMode === 'create') Create New Client
                            @elseif($modalMode === 'edit') Edit Client
                            @else View Client Details
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="saveClient">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Company Information -->
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-building me-2"></i>Company Information
                                    </h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" class="form-control @error('clientForm.company_name') is-invalid @enderror"
                                           wire:model="clientForm.company_name"
                                           placeholder="Client company name"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Company Address</label>
                                    <textarea class="form-control @error('clientForm.company_address') is-invalid @enderror"
                                              wire:model="clientForm.company_address" rows="3"
                                              placeholder="Full company address"
                                              @if($modalMode === 'view') readonly @endif></textarea>
                                    @error('clientForm.company_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Contact Information -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-address-book me-2"></i>Contact Information
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Primary Phone</label>
                                    <input type="text" class="form-control @error('clientForm.primary_phone') is-invalid @enderror"
                                           wire:model="clientForm.primary_phone"
                                           placeholder="Primary phone number"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.primary_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Secondary Phone</label>
                                    <input type="text" class="form-control @error('clientForm.secondary_phone') is-invalid @enderror"
                                           wire:model="clientForm.secondary_phone"
                                           placeholder="Secondary phone number"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.secondary_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control @error('clientForm.email') is-invalid @enderror"
                                           wire:model="clientForm.email"
                                           placeholder="company@example.com"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control @error('clientForm.website') is-invalid @enderror"
                                           wire:model="clientForm.website"
                                           placeholder="https://company.com"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Payment Information -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-credit-card me-2"></i>Payment Details
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">MPESA Account</label>
                                    <input type="text" class="form-control @error('clientForm.mpesa_account') is-invalid @enderror"
                                           wire:model="clientForm.mpesa_account"
                                           placeholder="MPESA phone number"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.mpesa_account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">MPESA Holder Name</label>
                                    <input type="text" class="form-control @error('clientForm.mpesa_holder_name') is-invalid @enderror"
                                           wire:model="clientForm.mpesa_holder_name"
                                           placeholder="Account holder name"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.mpesa_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control @error('clientForm.bank_name') is-invalid @enderror"
                                           wire:model="clientForm.bank_name"
                                           placeholder="Bank name"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Account</label>
                                    <input type="text" class="form-control @error('clientForm.bank_account') is-invalid @enderror"
                                           wire:model="clientForm.bank_account"
                                           placeholder="Account number"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.bank_account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Holder Name</label>
                                    <input type="text" class="form-control @error('clientForm.bank_holder_name') is-invalid @enderror"
                                           wire:model="clientForm.bank_holder_name"
                                           placeholder="Account holder name"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('clientForm.bank_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Additional Notes -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-sticky-note me-2"></i>Additional Information
                                    </h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea class="form-control @error('clientForm.additional_notes') is-invalid @enderror"
                                              wire:model="clientForm.additional_notes" rows="3"
                                              placeholder="Any additional notes or special instructions"
                                              @if($modalMode === 'view') readonly @endif></textarea>
                                    @error('clientForm.additional_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal" wire:loading.attr="disabled">
                                <i class="fas fa-times me-1"></i>{{ $modalMode === 'view' ? 'Close' : 'Cancel' }}
                            </button>
                            @if($modalMode !== 'view')
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveClient">
                                    <span wire:loading.remove wire:target="saveClient">
                                        <i class="fas fa-{{ $modalMode === 'create' ? 'plus' : 'save' }} me-1"></i>{{ $modalMode === 'create' ? 'Create Client' : 'Update Client' }}
                                    </span>
                                    <span wire:loading wire:target="saveClient">
                                        <i class="fas fa-spinner fa-spin me-1"></i>{{ $modalMode === 'create' ? 'Creating...' : 'Updating...' }}
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