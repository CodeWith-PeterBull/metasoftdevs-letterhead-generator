<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <!-- Bootstrap Icons (fallback) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
        <!-- Summernote CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote-bs5.min.css" rel="stylesheet">
        <!-- Summernote CSS fallback -->
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-bs5.min.css" rel="stylesheet">
        
        <!-- Custom CSS for better Summernote display -->
        <style>
            /* Enhanced list display in Summernote editor */
            .note-editable ul, .note-editable ol {
                margin: 12px 0 !important;
                padding-left: 30px !important;
            }
            
            .note-editable ul li {
                list-style-type: disc !important;
                margin-bottom: 6px !important;
                display: list-item !important;
            }
            
            .note-editable ol li {
                list-style-type: decimal !important;
                margin-bottom: 6px !important;
                display: list-item !important;
            }
            
            .note-editable ul ul li {
                list-style-type: circle !important;
            }
            
            .note-editable ol ol li {
                list-style-type: lower-alpha !important;
            }
            
            /* Enhanced table display in Summernote editor */
            .note-editable table {
                border-collapse: collapse !important;
                width: 100% !important;
                margin: 12px 0 !important;
                border: 1px solid #333 !important;
                table-layout: fixed !important;
            }
            
            .note-editable table th,
            .note-editable table td {
                border-right: 1px solid #333 !important;
                border-bottom: 1px solid #333 !important;
                padding: 8px 12px !important;
                text-align: left !important;
                vertical-align: top !important;
                word-wrap: break-word !important;
            }
            
            .note-editable table th:last-child,
            .note-editable table td:last-child {
                border-right: none !important;
            }
            
            .note-editable table tr:last-child th,
            .note-editable table tr:last-child td {
                border-bottom: none !important;
            }
            
            .note-editable table th {
                background-color: #f8f9fa !important;
                font-weight: 600 !important;
            }
            
            .note-editable table tr:nth-child(even) {
                background-color: #f8f9fa50 !important;
            }
            
            /* Ensure proper spacing around block elements */
            .note-editable p {
                margin-bottom: 12px !important;
            }
            
            .note-editable h1, .note-editable h2, .note-editable h3, 
            .note-editable h4, .note-editable h5, .note-editable h6 {
                margin-top: 20px !important;
                margin-bottom: 12px !important;
            }
            
            /* Enhanced toolbar contrast and visibility */
            .note-toolbar {
                border-bottom: 2px solid #dee2e6 !important;
                background-color: #f8f9fa !important;
                padding: 8px !important;
            }
            
            .note-toolbar .btn {
                color: #212529 !important;
                border: 1px solid #dee2e6 !important;
                background-color: #ffffff !important;
                margin: 1px !important;
                font-weight: 500 !important;
            }
            
            .note-toolbar .btn:hover {
                color: #ffffff !important;
                background-color: #0d6efd !important;
                border-color: #0d6efd !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            }
            
            .note-toolbar .btn:focus,
            .note-toolbar .btn:active,
            .note-toolbar .btn.active {
                color: #ffffff !important;
                background-color: #0b5ed7 !important;
                border-color: #0a58ca !important;
                box-shadow: 0 0 0 0.2rem rgba(49,132,253,0.25) !important;
            }
            
            .note-toolbar .dropdown-toggle::after {
                color: #212529 !important;
                font-weight: bold !important;
            }
            
            .note-toolbar .btn:hover .dropdown-toggle::after {
                color: #ffffff !important;
            }
            
            /* Dropdown menu styling */
            .note-toolbar .dropdown-menu {
                border: 2px solid #dee2e6 !important;
                box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
                background-color: #ffffff !important;
            }
            
            .note-toolbar .dropdown-item {
                color: #212529 !important;
                font-weight: 500 !important;
                padding: 8px 16px !important;
            }
            
            .note-toolbar .dropdown-item:hover,
            .note-toolbar .dropdown-item:focus {
                color: #ffffff !important;
                background-color: #0d6efd !important;
            }
            
            /* Color palette enhancements */
            .note-color .note-color-palette .note-color-row .note-color-col {
                border: 2px solid #dee2e6 !important;
                margin: 2px !important;
            }
            
            .note-color .note-color-palette .note-color-row .note-color-col:hover {
                transform: scale(1.1) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
                border-color: #0d6efd !important;
            }
            
            /* Logo Layout Stability - Prevent flickering */
            .application-logo {
                display: block !important;
                min-width: 2.25rem !important; /* h-9 equivalent */
                min-height: 2.25rem !important; /* w-9 equivalent */
                width: 2.25rem !important;
                height: 2.25rem !important;
            }
            
            /* Ensure navigation container stability */
            .shrink-0 {
                flex-shrink: 0 !important;
                min-width: fit-content !important;
            }
            
            /* Prevent layout shifts in navigation */
            nav .flex.items-center {
                min-height: 4rem !important; /* h-16 equivalent */
            }
        </style>
        
        @stack('styles')
        
        <!-- Vite Scripts for initial stability -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="min-h-screen">
            @auth
                @include('layouts.navigation')
            @else
                <!-- Simple header for non-authenticated users -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container">
                        <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
                    </div>
                </nav>
            @endauth

            <!-- Page Heading -->
            @hasSection('header')
                <header class="bg-white shadow mb-4">
                    <div class="container py-3">
                        @yield('header')
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>

        <!-- jQuery (required for Summernote) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Summernote JS with fallback -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote-bs5.min.js"></script>
        <script>
            // Fallback loader for Summernote
            if (typeof $.fn.summernote === 'undefined') {
                console.log('Primary Summernote CDN failed, loading fallback...');
                document.write('<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-bs5.min.js"><\/script>');
            }
        </script>
        
        @stack('scripts')
    </body>
</html>