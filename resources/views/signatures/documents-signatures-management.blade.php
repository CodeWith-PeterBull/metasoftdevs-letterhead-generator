@extends('layouts.letterhead')

@section('title', 'Signatures Management')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h4 mb-0">Document Signatures Management</h2>
                    </div>
                    <div class="card-body p-0">
                        <!-- Bootstrap 5 Tabs with Custom Styling -->
                        <style>
                            .nav-tabs .nav-link {
                                color: #6c757d !important;
                                border: 1px solid transparent;
                                border-bottom: 2px solid transparent;
                                background-color: transparent;
                                font-weight: 500;
                            }

                            .nav-tabs .nav-link:hover {
                                color: var(--bs-primary) !important;
                                border-bottom-color: var(--bs-primary);
                                background-color: rgba(var(--bs-primary-rgb), 0.1);
                            }

                            .nav-tabs .nav-link.active {
                                color: var(--bs-primary) !important;
                                border-bottom-color: var(--bs-primary);
                                background-color: #fff;
                                font-weight: 600;
                            }

                            .nav-tabs .nav-link i {
                                opacity: 0.8;
                            }

                            .nav-tabs .nav-link:hover i,
                            .nav-tabs .nav-link.active i {
                                opacity: 1;
                            }
                        </style>

                        <ul class="nav nav-tabs" id="invoiceManagementTabs" role="tablist">
                            {{-- <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab"
                                    data-bs-target="#invoices-tabpane" type="button" role="tab"
                                    aria-controls="invoices-tabpane" aria-selected="true">
                                    <i class="fas fa-file-invoice me-2"></i>Invoices
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="clients-tab" data-bs-toggle="tab"
                                    data-bs-target="#clients-tabpane" type="button" role="tab"
                                    aria-controls="clients-tabpane" aria-selected="false">
                                    <i class="fas fa-users me-2"></i>Clients
                                </button>
                            </li> --}}
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="signatures-tab" data-bs-toggle="tab"
                                    data-bs-target="#signatures-tabpane" type="button" role="tab"
                                    aria-controls="signatures-tabpane" aria-selected="false">
                                    <i class="fas fa-signature me-2"></i>Signatures
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="invoiceManagementTabsContent">
                            {{-- <div class="tab-pane fade show active" id="invoices-tabpane" role="tabpanel"
                                aria-labelledby="invoices-tab">
                                <div class="p-4">
                                    @livewire('invoice-management')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="clients-tabpane" role="tabpanel" aria-labelledby="clients-tab">
                                <div class="p-4">
                                    @livewire('client-management')
                                </div>
                            </div> --}}
                            <div class="tab-pane fade show active" id="signatures-tabpane" role="tabpanel"
                                aria-labelledby="signatures-tab">
                                <div class="p-4">
                                    @livewire('signature-management')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
