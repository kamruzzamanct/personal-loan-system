<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Personal Loan System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <style>{!! file_get_contents(resource_path('css/style.css')) !!}</style>
    @endif
</head>
<body>
    @php
        $authenticatedUser = auth()->user();
        $adminLink = $authenticatedUser && method_exists($authenticatedUser, 'hasAdminRole') && $authenticatedUser->hasAdminRole()
            ? route('admin.dashboard')
            : route('admin.login');
    @endphp

    <header class="site-header">
        <div class="site-shell site-header-inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark">PLS</span>
                <span>Personal Loan System</span>
            </a>

            <button type="button" class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="site-nav-wrap">
                Menu
            </button>

            <div id="site-nav-wrap" class="site-nav-wrap" data-menu-panel>
                <nav class="site-nav" aria-label="Main menu">
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('loan-applications.create') }}">Apply</a>
                    <a href="{{ route('home') }}#benefits">Benefits</a>
                    <a href="{{ route('home') }}#faq">FAQ</a>
                </nav>
            </div>

            <a href="{{ route('loan-applications.create') }}" class="menu-action">Apply Now</a>
        </div>
    </header>

    <main>
        <div class="site-shell">
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="site-shell">
            <small>Secure personal loan intake and risk pre-screening.</small>
        </div>
    </footer>

    <script>
        const toggle = document.querySelector('[data-menu-toggle]');
        const panel = document.querySelector('[data-menu-panel]');

        if (toggle && panel) {
            toggle.addEventListener('click', () => {
                const isOpen = panel.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }
    </script>
</body>
</html>
