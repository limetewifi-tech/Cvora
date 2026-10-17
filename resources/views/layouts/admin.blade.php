<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('document-title')@yield('document-title') — Administration @else Administration CVora @endif</title>
</head>
<body>
    <div class="cvora-shell">
        <aside class="cvora-sidebar cvora-sidebar-admin offcanvas-lg offcanvas-start" tabindex="-1" id="admin-nav" aria-label="Administration">
            <div class="offcanvas-header">
                <a href="{{ route('admin.index') }}" aria-label="Administration CVora">@include('components.logo', ['variant' => 'light'])</a>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#admin-nav" aria-label="Fermer le menu"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column px-3 pb-4">
                <nav class="nav flex-column gap-1">
                    @foreach ([
                        'admin.index' => ['Vue d\'ensemble', 'bi-speedometer2'],
                        'admin.users' => ['Comptes', 'bi-people'],
                        'admin.plans' => ['Offres et tarifs', 'bi-tags'],
                        'admin.templates' => ['Modèles', 'bi-layout-text-window'],
                        'admin.transactions' => ['Transactions', 'bi-receipt'],
                        'admin.subscriptions' => ['Abonnements', 'bi-credit-card'],
                        'admin.ai' => ['Quotas IA', 'bi-stars'],
                        'admin.settings' => ['Configuration', 'bi-sliders'],
                        'admin.errors' => ['Journaux', 'bi-exclamation-triangle'],
                    ] as $route => [$label, $icon])
                        <a href="{{ route($route) }}" class="cvora-nav-link {{ request()->routeIs($route) ? 'active' : '' }}" @if (request()->routeIs($route)) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                        </a>
                    @endforeach
                </nav>
                <a href="{{ route('dashboard') }}" class="cvora-nav-link mt-4"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour à l'espace personnel</a>
            </div>
        </aside>
        <div class="min-w-0">
            <header class="cvora-topbar">
                <button type="button" class="btn btn-outline-primary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#admin-nav" aria-controls="admin-nav" aria-label="Ouvrir l'administration">
                    <i class="bi bi-list" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Administration</span>
                </button>
                <p class="mb-0 small text-secondary">Espace administrateur</p>
                @include('partials.theme-toggle')
            </header>
            <main id="contenu" class="cvora-content">
                @include('components.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
