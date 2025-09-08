<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MetaSoft Letterhead Generator') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            font-weight: normal;
            line-height: 1.7;
            background: #fff;
            color: #687280;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header */
        .header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 600;
            color: #ff2d20;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            color: #687280;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: #ff2d20;
            background: #fef2f2;
        }

        .btn-primary {
            background: #ff2d20;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #dc2626;
        }

        /* Hero Section */
        .hero {
            padding: 4rem 0;
            text-align: center;
            background: linear-gradient(135deg, #fef2f2 0%, #fafafa 100%);
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 1rem 0;
            line-height: 1.1;
        }

        .hero p {
            font-size: 1.25rem;
            color: #6b7280;
            margin: 0 0 2rem 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-outline {
            border: 2px solid #ff2d20;
            color: #ff2d20;
            background: transparent;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: #ff2d20;
            color: white;
        }

        .btn-large {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        /* Features Section */
        .features {
            padding: 4rem 0;
        }

        .features h2 {
            text-align: center;
            font-size: 2.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 3rem 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .feature-card:hover {
            border-color: #ff2d20;
            box-shadow: 0 4px 12px rgba(255, 45, 32, 0.1);
        }

        .feature-icon {
            width: 3rem;
            height: 3rem;
            background: #fef2f2;
            color: #ff2d20;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 0.5rem 0;
        }

        .feature-card p {
            margin: 0;
            color: #6b7280;
        }

        /* Stats */
        .stats {
            background: #f9fafb;
            padding: 3rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat {
            padding: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ff2d20;
            display: block;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Footer */
        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 2rem 0;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-logo {
            color: #ff2d20;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .footer-text {
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1.125rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ url('/') }}" class="logo">
                    MetaSoft Letterheads
                </a>

                @if (Route::has('login'))
                    <div class="nav-links">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Professional Letterhead Generator</h1>
            <p>Create stunning, professional letterheads in minutes. Perfect for businesses, organizations, and
                professionals.</p>

            <div class="hero-buttons">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary btn-large">Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary btn-large">Create Free Account</a>
                    <a href="{{ route('login') }}" class="btn-outline btn-large">Sign In</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat">
                    <span class="stat-number">100+</span>
                    <div class="stat-label">Templates Available</div>
                </div>
                <div class="stat">
                    <span class="stat-number">50+</span>
                    <div class="stat-label">Companies Trust Us</div>
                </div>
                <div class="stat">
                    <span class="stat-number">1,000+</span>
                    <div class="stat-label">Letterheads Generated</div>
                </div>
                <div class="stat">
                    <span class="stat-number">99%</span>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Choose MetaSoft Letterheads?</h2>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Company Management</h3>
                    <p>Manage multiple companies with logos, contact information, and branding details. Easy-to-use
                        interface for quick setup.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">✏️</div>
                    <h3>Rich Text Editor</h3>
                    <p>Powerful editor with tables, lists, formatting options, and more. Create professional documents
                        with ease.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h3>PDF Generation</h3>
                    <p>Generate high-quality PDF letterheads instantly. Multiple paper sizes supported including US
                        Letter, A4, and Legal.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3>Professional Templates</h3>
                    <p>Choose from professionally designed templates or create your own. Customizable layouts to match
                        your brand.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🖼️</div>
                    <h3>Media Management</h3>
                    <p>Upload and manage company logos and other media assets. Automatic optimization and format
                        support.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">💝</div>
                    <h3>User-Friendly</h3>
                    <p>Intuitive design that works perfectly on desktop, tablet, and mobile devices. Clean and
                        professional interface.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">MetaSoft Letterheads</div>
                <div class="footer-text">
                    © {{ date('Y') }} MetaSoft Letterheads. By <a style="color: red" href="https://metsoftdevs.com"
                        target="_blank" rel="noopener" class="underline hover:text-white">Meta Software Developers</a>.
                    All rights
                    reserved.
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
