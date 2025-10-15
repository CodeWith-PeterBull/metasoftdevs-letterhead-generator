<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Invoice Management</h2>
        <button type="button" class="btn btn-primary" wire:click="createInvoice" wire:loading.attr="disabled" wire:target="createInvoice">
            <span wire:loading.remove wire:target="createInvoice"><i class="fas fa-plus-circle me-1"></i> Create Invoice</span>
            <span wire:loading wire:target="createInvoice"><i class="fas fa-spinner fa-spin me-1"></i> Loading...</span>
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
                        <input type="text" class="form-control" placeholder="Search invoices..." 
                               wire:model.live="searchTerm">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="all">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr wire:key="invoice-{{ $invoice->id }}">
                                    <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->invoiceTo->company_name }}</td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($invoice->due_date)
                                            <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                                {{ $invoice->due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($invoice->status === 'paid') bg-success
                                            @elseif($invoice->status === 'sent') bg-info
                                            @elseif($invoice->status === 'draft') bg-secondary
                                            @elseif($invoice->status === 'overdue') bg-danger
                                            @else bg-warning
                                            @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    wire:click="viewInvoice({{ $invoice->id }})" 
                                                    title="View Invoice"
                                                    wire:loading.attr="disabled"
                                                    wire:target="viewInvoice({{ $invoice->id }})">
                                                <span wire:loading.remove wire:target="viewInvoice({{ $invoice->id }})"><i class="fas fa-eye me-1"></i><span class="d-none d-md-inline">View</span></span>
                                                <span wire:loading wire:target="viewInvoice({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-1"></i>...</span>
                                            </button>
                                            
                                            <!-- PDF Actions -->
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                                        data-bs-toggle="dropdown" 
                                                        title="PDF Actions">
                                                    <i class="fas fa-file-pdf me-1"></i><span class="d-none d-lg-inline">PDF</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item" 
                                                                wire:click="generatePdf({{ $invoice->id }})" 
                                                                wire:loading.attr="disabled"
                                                                wire:target="generatePdf({{ $invoice->id }})"
                                                                type="button">
                                                            <span wire:loading.remove wire:target="generatePdf({{ $invoice->id }})"><i class="fas fa-file-pdf me-2"></i>Generate PDF</span>
                                                            <span wire:loading wire:target="generatePdf({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-2"></i>Generating...</span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" 
                                                                wire:click="viewPdf({{ $invoice->id }})" 
                                                                wire:loading.attr="disabled"
                                                                wire:target="viewPdf({{ $invoice->id }})"
                                                                type="button">
                                                            <span wire:loading.remove wire:target="viewPdf({{ $invoice->id }})"><i class="fas fa-external-link-alt me-2"></i>View PDF</span>
                                                            <span wire:loading wire:target="viewPdf({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" 
                                                                wire:click="downloadPdf({{ $invoice->id }})" 
                                                                wire:loading.attr="disabled"
                                                                wire:target="downloadPdf({{ $invoice->id }})"
                                                                type="button">
                                                            <span wire:loading.remove wire:target="downloadPdf({{ $invoice->id }})"><i class="fas fa-download me-2"></i>Download PDF</span>
                                                            <span wire:loading wire:target="downloadPdf({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-2"></i>Downloading...</span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>

                                            @if($invoice->status !== 'paid')
                                                @if(in_array($invoice->status, ['draft', 'sent']))
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            wire:click="editInvoice({{ $invoice->id }})"
                                                            title="Edit Invoice"
                                                            wire:loading.attr="disabled"
                                                            wire:target="editInvoice({{ $invoice->id }})">
                                                        <span wire:loading.remove wire:target="editInvoice({{ $invoice->id }})"><i class="fas fa-edit me-1"></i><span class="d-none d-xl-inline">Edit</span></span>
                                                        <span wire:loading wire:target="editInvoice({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-1"></i>...</span>
                                                    </button>
                                                @endif

                                                @if($invoice->status === 'draft')
                                                    <button type="button" class="btn btn-outline-info"
                                                            wire:click="markAsSent({{ $invoice->id }})"
                                                            title="Mark as Sent"
                                                            wire:loading.attr="disabled"
                                                            wire:target="markAsSent({{ $invoice->id }})"
                                                            onclick="return confirm('Mark this invoice as sent?')">
                                                        <span wire:loading.remove wire:target="markAsSent({{ $invoice->id }})"><i class="fas fa-paper-plane me-1"></i><span class="d-none d-xl-inline">Send</span></span>
                                                        <span wire:loading wire:target="markAsSent({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-1"></i>...</span>
                                                    </button>
                                                @endif

                                                @if($invoice->status === 'sent')
                                                    <button type="button" class="btn btn-outline-success"
                                                            wire:click="markAsPaid({{ $invoice->id }})"
                                                            title="Mark as Paid"
                                                            wire:loading.attr="disabled"
                                                            wire:target="markAsPaid({{ $invoice->id }})"
                                                            onclick="return confirm('Mark this invoice as paid?')">
                                                        <span wire:loading.remove wire:target="markAsPaid({{ $invoice->id }})"><i class="fas fa-check-circle me-1"></i><span class="d-none d-xl-inline">Paid</span></span>
                                                        <span wire:loading wire:target="markAsPaid({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-1"></i>...</span>
                                                    </button>
                                                @endif
                                            @endif
                                            <button type="button" class="btn btn-outline-danger" 
                                                    wire:click="confirmDelete({{ $invoice->id }})" 
                                                    title="Delete Invoice"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmDelete({{ $invoice->id }})">
                                                <span wire:loading.remove wire:target="confirmDelete({{ $invoice->id }})"><i class="fas fa-trash me-1"></i><span class="d-none d-xl-inline">Delete</span></span>
                                                <span wire:loading wire:target="confirmDelete({{ $invoice->id }})"><i class="fas fa-spinner fa-spin me-1"></i>...</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-receipt-cutoff text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No invoices found</h5>
                    <p class="text-muted mb-4">Get started by creating your first invoice.</p>
                    <button type="button" class="btn btn-primary" wire:click="createInvoice" wire:loading.attr="disabled" wire:target="createInvoice">
                        <span wire:loading.remove wire:target="createInvoice"><i class="bi bi-plus-circle"></i> Create Invoice</span>
                        <span wire:loading wire:target="createInvoice"><i class="fas fa-spinner fa-spin me-1"></i> Loading...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center">
                            <h5 class="modal-title me-3 mb-0">
                                @if($modalMode === 'create') Create New Invoice
                                @elseif($modalMode === 'edit') Edit Invoice
                                @else View Invoice
                                @endif
                            </h5>
                            @if($modalMode === 'view' && $selectedInvoice)
                                <span class="badge fs-6
                                    @if($invoiceForm['status'] === 'paid') bg-success
                                    @elseif($invoiceForm['status'] === 'sent') bg-info
                                    @elseif($invoiceForm['status'] === 'draft') bg-secondary
                                    @elseif($invoiceForm['status'] === 'overdue') bg-danger
                                    @else bg-warning
                                    @endif">
                                    {{ ucfirst($invoiceForm['status']) }}
                                </span>
                            @endif
                        </div>
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

                    <form wire:submit.prevent="saveInvoice">
                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Invoice Number *</label>
                                    <input type="text" class="form-control @error('invoiceForm.invoice_number') is-invalid @enderror" 
                                           wire:model="invoiceForm.invoice_number"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('invoiceForm.invoice_number') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Company *</label>
                                    <select class="form-select @error('invoiceForm.company_id') is-invalid @enderror" 
                                            wire:model="invoiceForm.company_id"
                                            @if($modalMode === 'view') disabled @endif>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('invoiceForm.company_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Invoice Date *</label>
                                    <input type="date" class="form-control @error('invoiceForm.invoice_date') is-invalid @enderror" 
                                           wire:model="invoiceForm.invoice_date"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('invoiceForm.invoice_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" class="form-control @error('invoiceForm.due_date') is-invalid @enderror" 
                                           wire:model="invoiceForm.due_date"
                                           @if($modalMode === 'view') readonly @endif>
                                    @error('invoiceForm.due_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Client *</label>
                                        @if($modalMode !== 'view')
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openClientModal" wire:loading.attr="disabled" wire:target="openClientModal">
                                                <span wire:loading.remove wire:target="openClientModal"><i class="bi bi-plus"></i> Add New Client</span>
                                                <span wire:loading wire:target="openClientModal"><i class="fas fa-spinner fa-spin"></i> Loading...</span>
                                            </button>
                                        @endif
                                    </div>
                                    <select class="form-select @error('invoiceForm.invoice_to_id') is-invalid @enderror" 
                                            wire:model="invoiceForm.invoice_to_id"
                                            @if($modalMode === 'view') disabled @endif>
                                        <option value="">Select Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('invoiceForm.invoice_to_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Invoice Items</h6>
                                        @if($modalMode !== 'view')
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem">
                                                <span wire:loading.remove wire:target="addItem"><i class="fas fa-plus me-1"></i>Add Item</span>
                                                <span wire:loading wire:target="addItem"><i class="fas fa-spinner fa-spin me-1"></i>Adding...</span>
                                            </button>
                                        @endif
                                    </div>

                                    @if(count($items) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Service</th>
                                                        <th>Description</th>
                                                        <th width="80">Qty</th>
                                                        <th width="100">Unit Price</th>
                                                        <th width="80">Tax %</th>
                                                        <th width="100">Total</th>
                                                        @if($modalMode !== 'view')
                                                            <th width="50">Action</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $index => $item)
                                                        <tr wire:key="item-{{ $index }}">
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm" 
                                                                       wire:model.live="items.{{ $index }}.service_name"
                                                                       placeholder="Service name"
                                                                       @if($modalMode === 'view') readonly @endif>
                                                                @error('items.' . $index . '.service_name') 
                                                                    <small class="text-danger">{{ $message }}</small> 
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <textarea class="form-control form-control-sm" rows="1" 
                                                                          wire:model="items.{{ $index }}.description"
                                                                          placeholder="Description"
                                                                          @if($modalMode === 'view') readonly @endif></textarea>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0.01" 
                                                                       class="form-control form-control-sm" 
                                                                       wire:model.live="items.{{ $index }}.quantity"
                                                                       @if($modalMode === 'view') readonly @endif>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0" 
                                                                       class="form-control form-control-sm" 
                                                                       wire:model.live="items.{{ $index }}.unit_price"
                                                                       @if($modalMode === 'view') readonly @endif>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0" max="100" 
                                                                       class="form-control form-control-sm" 
                                                                       wire:model.live="items.{{ $index }}.tax_rate"
                                                                       @if($modalMode === 'view') readonly @endif>
                                                            </td>
                                                            <td class="fw-bold">
                                                                {{ number_format((floatval($item['quantity'] ?? 0) ?: 0) * (floatval($item['unit_price'] ?? 0) ?: 0) * (1 + ((floatval($item['tax_rate'] ?? 0) ?: 0) / 100)), 2) }}
                                                            </td>
                                                            @if($modalMode !== 'view')
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                            wire:click="removeItem({{ $index }})"
                                                                            title="Remove Item">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4 border rounded">
                                            <p class="text-muted mb-0">No items added yet</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" rows="3" 
                                                      wire:model="invoiceForm.notes"
                                                      placeholder="Additional notes for client"
                                                      @if($modalMode === 'view') readonly @endif></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title">Invoice Totals</h6>
                                                    <div class="row mb-2">
                                                        <div class="col">Subtotal:</div>
                                                        <div class="col-auto fw-bold">{{ $invoiceForm['currency'] }} {{ number_format($invoiceForm['sub_total'], 2) }}</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col">Tax:</div>
                                                        <div class="col-auto fw-bold">{{ $invoiceForm['currency'] }} {{ number_format($invoiceForm['tax_amount'], 2) }}</div>
                                                    </div>
                                                    <hr>
                                                    <div class="row mb-2">
                                                        <div class="col"><strong>Grand Total:</strong></div>
                                                        <div class="col-auto"><strong>{{ $invoiceForm['currency'] }} {{ number_format($invoiceForm['grand_total'], 2) }}</strong></div>
                                                    </div>
                                                    @if($modalMode !== 'view')
                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label class="form-label small mb-0">Paid Amount:</label>
                                                            </div>
                                                            <div class="col-auto">
                                                                <input type="number" step="0.01" min="0"
                                                                       max="{{ $invoiceForm['grand_total'] }}"
                                                                       class="form-control form-control-sm text-end @error('invoiceForm.paid_amount') is-invalid @enderror"
                                                                       style="width: 120px;"
                                                                       wire:model.live="invoiceForm.paid_amount"
                                                                       placeholder="0.00">
                                                                @error('invoiceForm.paid_amount')
                                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    @else
                                                        @if($invoiceForm['paid_amount'] > 0)
                                                            <div class="row mb-2">
                                                                <div class="col">Paid Amount:</div>
                                                                <div class="col-auto fw-bold text-success">-{{ $invoiceForm['currency'] }} {{ number_format($invoiceForm['paid_amount'], 2) }}</div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if($invoiceForm['paid_amount'] > 0)
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col"><strong>Balance Due:</strong></div>
                                                            <div class="col-auto">
                                                                <strong class="{{ $invoiceForm['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                                    {{ $invoiceForm['currency'] }} {{ number_format($invoiceForm['balance'], 2) }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($modalMode !== 'view')
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small">Status Override</label>
                                                <select class="form-select form-select-sm @error('invoiceForm.status') is-invalid @enderror"
                                                        wire:model="invoiceForm.status">
                                                    <option value="draft">Draft</option>
                                                    <option value="sent">Sent</option>
                                                    <option value="paid">Paid</option>
                                                    <option value="overdue">Overdue</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                                @error('invoiceForm.status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Manual status override (optional)</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal" wire:loading.attr="disabled">
                                <i class="fas fa-times me-1"></i>{{ $modalMode === 'view' ? 'Close' : 'Cancel' }}
                            </button>
                            @if($modalMode !== 'view')
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveInvoice">
                                    <span wire:loading.remove wire:target="saveInvoice">
                                        <i class="fas fa-{{ $modalMode === 'create' ? 'plus' : 'save' }} me-1"></i>{{ $modalMode === 'create' ? 'Create Invoice' : 'Update Invoice' }}
                                    </span>
                                    <span wire:loading wire:target="saveInvoice">
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

    @if($showClientModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Client</h5>
                        <button type="button" class="btn-close" wire:click="closeClientModal"></button>
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
                                <div class="col-12">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" class="form-control @error('clientForm.company_name') is-invalid @enderror" 
                                           wire:model="clientForm.company_name" placeholder="Client company name">
                                    @error('clientForm.company_name') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Company Address</label>
                                    <textarea class="form-control @error('clientForm.company_address') is-invalid @enderror" 
                                              wire:model="clientForm.company_address" rows="3" 
                                              placeholder="Full company address"></textarea>
                                    @error('clientForm.company_address') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Primary Phone</label>
                                    <input type="text" class="form-control @error('clientForm.primary_phone') is-invalid @enderror" 
                                           wire:model="clientForm.primary_phone" placeholder="Primary phone number">
                                    @error('clientForm.primary_phone') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Secondary Phone</label>
                                    <input type="text" class="form-control @error('clientForm.secondary_phone') is-invalid @enderror" 
                                           wire:model="clientForm.secondary_phone" placeholder="Secondary phone number">
                                    @error('clientForm.secondary_phone') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control @error('clientForm.email') is-invalid @enderror" 
                                           wire:model="clientForm.email" placeholder="company@example.com">
                                    @error('clientForm.email') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control @error('clientForm.website') is-invalid @enderror" 
                                           wire:model="clientForm.website" placeholder="https://company.com">
                                    @error('clientForm.website') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">Payment Details</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">MPESA Account</label>
                                    <input type="text" class="form-control @error('clientForm.mpesa_account') is-invalid @enderror" 
                                           wire:model="clientForm.mpesa_account" placeholder="MPESA phone number">
                                    @error('clientForm.mpesa_account') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">MPESA Holder Name</label>
                                    <input type="text" class="form-control @error('clientForm.mpesa_holder_name') is-invalid @enderror" 
                                           wire:model="clientForm.mpesa_holder_name" placeholder="Account holder name">
                                    @error('clientForm.mpesa_holder_name') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control @error('clientForm.bank_name') is-invalid @enderror" 
                                           wire:model="clientForm.bank_name" placeholder="Bank name">
                                    @error('clientForm.bank_name') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Account</label>
                                    <input type="text" class="form-control @error('clientForm.bank_account') is-invalid @enderror" 
                                           wire:model="clientForm.bank_account" placeholder="Account number">
                                    @error('clientForm.bank_account') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Bank Holder Name</label>
                                    <input type="text" class="form-control @error('clientForm.bank_holder_name') is-invalid @enderror" 
                                           wire:model="clientForm.bank_holder_name" placeholder="Account holder name">
                                    @error('clientForm.bank_holder_name') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea class="form-control @error('clientForm.additional_notes') is-invalid @enderror" 
                                              wire:model="clientForm.additional_notes" rows="3" 
                                              placeholder="Any additional notes or special instructions"></textarea>
                                    @error('clientForm.additional_notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeClientModal" wire:loading.attr="disabled">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveClient">
                                <span wire:loading.remove wire:target="saveClient">
                                    <i class="fas fa-user-plus me-1"></i>Create Client
                                </span>
                                <span wire:loading wire:target="saveClient">
                                    <i class="fas fa-spinner fa-spin me-1"></i>Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1070;">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this invoice?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete" wire:loading.attr="disabled">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteInvoice" wire:loading.attr="disabled" wire:target="deleteInvoice">
                            <span wire:loading.remove wire:target="deleteInvoice">
                                <i class="fas fa-trash me-1"></i>Delete
                            </span>
                            <span wire:loading wire:target="deleteInvoice">
                                <i class="fas fa-spinner fa-spin me-1"></i>Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript for PDF Actions -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Handle PDF view
            Livewire.on('view-pdf', function (data) {
                window.open(data[0].url, '_blank');
            });

            // Handle PDF download
            Livewire.on('download-pdf', function (data) {
                const link = document.createElement('a');
                link.href = data[0].url;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Handle PDF generation success
            Livewire.on('pdf-generated', function (data) {
                console.log('PDF generated successfully:', data[0].path);
            });
        });
    </script>
</div>
