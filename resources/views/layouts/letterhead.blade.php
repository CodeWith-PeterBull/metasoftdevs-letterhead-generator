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
        
        <!-- Summernote CSS -->
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
        <!-- Summernote CSS fallback -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
        
        @stack('styles')
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
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
        <script>
            // Fallback loader for Summernote
            if (typeof $.fn.summernote === 'undefined') {
                console.log('Primary Summernote CDN failed, loading fallback...');
                document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"><\/script>');
            }
        </script>
        
        @stack('scripts')
        
        <!-- Vite Scripts (loaded last to avoid conflicts) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </body>
</html>