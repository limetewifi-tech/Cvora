<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('document-title')@yield('document-title') — CVora @else Espace CVora @endif</title>
</head>
<body>
    <a href="#contenu" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-light">Aller au contenu</a>
    <div class="cvora-shell">
        <aside class="cvora-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Espace personnel">
            <div class="offcanvas-header">
                <a href="{{ route('dashboard') }}" aria-label="Tableau de bord CVora">@include('components.logo')</a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Fermer le menu"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column px-3 pb-4">
                <nav class="nav flex-column gap-1" aria-label="Navigation principale">
                    @php
                        $primary = [
                            ['templates.index', 'Modèles', 'templates.*', 'bi-grid-3x3-gap'],
                            ['cvs.index', 'Mes CV', ['cvs.*', 'imports.*'], 'bi-file-earmark-text'],
                            ['letters.index', 'Lettres', 'letters.*', 'bi-envelope-paper'],
                            ['assistant.index', 'Assistant', 'assistant.*', 'bi-stars'],
                        ];
                        $secondary = [
                            ['dashboard', 'Tableau de bord', 'dashboard', 'bi-house'],
                            ['imports.create', 'Importer un CV', 'imports.*', 'bi-upload'],
                            ['studio.index', 'Adapter à une offre', 'studio.*', 'bi-bullseye'],
                            ['applications.index', 'Candidatures', 'applications.*', 'bi-briefcase'],
                            ['billing.index', 'Abonnement', 'billing.*', 'bi-credit-card'],
                            ['profile.edit', 'Compte', 'profile.*', 'bi-person'],
                            ['settings.edit', 'Paramètres', 'settings.*', 'bi-gear'],
                        ];
                    @endphp
                    @foreach ($primary as [$route, $label, $pattern, $icon])
                        <a href="{{ route($route) }}" class="cvora-nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                        </a>
                    @endforeach
                    <p class="cvora-nav-label mb-0 mt-3">Aussi disponibles</p>
                    @foreach ($secondary as [$route, $label, $pattern, $icon])
                        <a href="{{ route($route) }}" class="cvora-nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                        </a>
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button class="btn btn-outline-secondary w-100" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Déconnexion</button>
                </form>
            </div>
        </aside>
        <div class="min-w-0">
            <header class="cvora-topbar">
                <button type="button" class="btn btn-outline-primary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Ouvrir la navigation">
                    <i class="bi bi-list" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Navigation</span>
                </button>
                <p class="mb-0 small text-secondary text-truncate d-none d-md-block">{{ auth()->user()->name }}</p>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    @include('partials.theme-toggle')
                    <a class="btn btn-primary" href="{{ route('templates.index') }}" aria-label="Choisir un modèle"><i class="bi bi-plus-lg d-sm-none" aria-hidden="true"></i><span class="d-none d-sm-inline">Choisir un modèle</span></a>
                </div>
            </header>
            <main id="contenu" class="cvora-content">
                @include('components.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
