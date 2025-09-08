@extends('layouts.letterhead')

@section('title', 'Company Management - ' . config('app.name'))

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Company Management</h2>
            <p class="text-muted mb-0">Manage your company profiles and branding information</p>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Companies</li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="container">
        <livewire:company-management />
    </div>
@endsection

@push('styles')
    <style>
        /* Custom styles for company management */
        .company-logo-preview {
            transition: transform 0.2s ease-in-out;
        }

        .company-logo-preview:hover {
            transform: scale(1.05);
        }

        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-top: 0;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            font-size: 0.75rem;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: 1px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .toast-container {
            z-index: 1060;
        }

        /* Loading spinner overlay */
        .position-fixed.top-50.start-50 {
            z-index: 2000;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50px;
            padding: 20px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-hide success toasts
            setTimeout(function() {
                var toasts = document.querySelectorAll('.toast');
                toasts.forEach(function(toast) {
                    if (toast.querySelector('.toast-header:not(.bg-danger)')) {
                        var bsToast = new bootstrap.Toast(toast);
                        bsToast.hide();
                    }
                });
            }, 5000);

            // Handle modal cleanup
            document.addEventListener('hidden.bs.modal', function(event) {
                if (event.target.id === 'companyModal') {
                    // Reset active tab to first tab
                    var firstTab = document.querySelector('#basic-tab');
                    if (firstTab) {
                        var tab = new bootstrap.Tab(firstTab);
                        tab.show();
                    }
                }
            });

            // Handle duplicate modal
            document.addEventListener('livewire:init', function() {
                Livewire.on('show-duplicate-modal', function(companyId) {
                    var modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
                    modal.show();
                });
            });
        });
    </script>
@endpush
