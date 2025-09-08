@extends('layouts.letterhead')

@section('title', 'Dashboard - MetaSoft Letterheads')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 mb-1 text-dark">Dashboard</h1>
                <p class="text-muted mb-0">Welcome to your letterhead management center</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('letterhead.form') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>
                    Create Letterhead
                </a>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="h5 fw-bold text-dark mb-2">Welcome to MetaSoft Letterheads!</h3>
                    <p class="text-muted mb-0">Create professional letterheads with ease using our powerful tools.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('letterhead.form') }}" class="btn btn-success">
                        <i class="fas fa-magic me-2"></i>
                        Start Creating
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-building text-primary fs-5"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Companies</h5>
                            <p class="text-muted small mb-0">Manage your business profiles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-file-alt text-success fs-5"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Letterheads</h5>
                            <p class="text-muted small mb-0">Professional documents created</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-palette text-info fs-5"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Templates</h5>
                            <p class="text-muted small mb-0">Ready-to-use designs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Management Section -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Company Management</h5>
                <small class="text-muted">Quick access to manage your companies</small>
            </div>
        </div>
        <div class="card-body">
            <!-- Embed Company Management Livewire Component -->
            @livewire('company-management')
        </div>
    </div>
</div>
@endsection
