<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <style>{!! file_get_contents(resource_path('css/style.css')) !!}</style>
    @endif
</head>
<body class="admin-ui">
    @php
        $adminUser = auth()->user();
        $adminName = $adminUser?->name ?? 'Admin User';
        $adminEmail = $adminUser?->email ?? 'admin@company.com';
        $adminRoleRaw = $adminUser?->role;
        $adminRoleValue = $adminRoleRaw instanceof \BackedEnum ? $adminRoleRaw->value : (string) $adminRoleRaw;
        $adminRoleLabel = $adminRoleValue !== '' ? ucwords(str_replace('_', ' ', $adminRoleValue)) : 'Admin';
        $adminInitials = collect(explode(' ', trim($adminName)))
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="admin-shell" data-admin-shell>
        <aside class="admin-sidebar" data-admin-sidebar>
            <div class="admin-brand">
                <div class="admin-brand-mark">PLS</div>
                <div>
                    <strong>Loan Admin</strong>
                    <p>Operations Console</p>
                </div>
            </div>

            <div class="admin-profile">
                <div class="admin-avatar">{{ $adminInitials !== '' ? $adminInitials : 'AU' }}</div>
                <div class="admin-profile-info">
                    <strong>{{ $adminName }}</strong>
                    <span>{{ $adminRoleLabel }}</span>
                </div>
            </div>

            <nav class="admin-menu" aria-label="Admin navigation">
                <a href="{{ route('admin.dashboard') }}" class="admin-menu-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.loan-applications.index') }}" class="admin-menu-link {{ request()->routeIs('admin.loan-applications.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2Zm8 1.5V8h4.5L14 3.5ZM8 12h8v1.5H8V12Zm0 4h8v1.5H8V16Z"/></svg>
                    <span>Applications</span>
                </a>
                @can('manage users')
                    <a href="{{ route('admin.users.index') }}" class="admin-menu-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.57 2.99-3.5S17.66 4 16 4s-3 1.57-3 3.5 1.34 3.5 3 3.5Zm-8 0c1.66 0 2.99-1.57 2.99-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11Zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.98 1.97 3.45V20h6v-3.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>
                        <span>Users</span>
                    </a>
                @endcan
                <a href="{{ route('loan-applications.create') }}" class="admin-menu-link">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.01 10.01 0 0 0 12 2Zm1 5v4h4v2h-4v4h-2v-4H7v-2h4V7Z"/></svg>
                    <span>New Application</span>
                </a>
                <a href="{{ route('home') }}" class="admin-menu-link">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2 11h2v10h6v-6h4v6h6V11h2L12 3Zm-1 16H6v-7.5L11 7v12Zm7 0h-5V7l5 4.5V19Z"/></svg>
                    <span>Public Site</span>
                </a>
            </nav>

            <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout-form">
                @csrf
                <button type="submit" class="admin-logout-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m17 7-1.4 1.4 2.6 2.6H8v2h10.2l-2.6 2.6L17 17l5-5-5-5ZM4 5h7V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7v-2H4V5Z"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-sidebar-toggle" type="button" data-admin-toggle aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/></svg>
                </button>
                <div class="admin-topbar-meta">
                    <p>Admin Workspace</p>
                    <small>{{ now()->format('l, M d, Y H:i') }}</small>
                </div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const toggleButton = document.querySelector('[data-admin-toggle]');
        const shell = document.querySelector('[data-admin-shell]');

        if (toggleButton && shell) {
            toggleButton.addEventListener('click', () => {
                shell.classList.toggle('sidebar-open');
            });
        }
    </script>
</body>
</html>
