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
                <nav class="nav flex-column gap-1">
                    @php
                        $links = [
                            ['dashboard', 'Vue d\'ensemble', 'dashboard', 'bi-grid'],
                            ['cvs.index', 'Mes CV', ['cvs.index', 'cvs.edit', 'cvs.preview', 'cvs.versions', 'cvs.delete'], 'bi-file-earmark-text'],
                            ['cvs.create', 'Créer un CV', 'cvs.create', 'bi-plus-circle'],
                            ['imports.create', 'Importer un CV', 'imports.*', 'bi-upload'],
                            ['letters.index', 'Lettres de motivation', 'letters.*', 'bi-envelope-paper'],
                            ['assistant.index', 'Assistant IA', 'assistant.*', 'bi-stars'],
                            ['studio.index', 'Studio CV', 'studio.*', 'bi-bullseye'],
                            ['applications.index', 'Mes candidatures', 'applications.*', 'bi-briefcase'],
                            ['jobs.index', 'Offres d\'emploi', 'jobs.*', 'bi-megaphone'],
                            ['templates.index', 'Modèles', 'templates.*', 'bi-layout-text-window'],
                            ['profile.edit', 'Mon profil', 'profile.*', 'bi-person'],
                            ['billing.index', 'Abonnement et facturation', 'billing.*', 'bi-credit-card'],
                            ['settings.edit', 'Paramètres', 'settings.*', 'bi-gear'],
                        ];
                    @endphp
                    @foreach ($links as [$route, $label, $pattern, $icon])
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
                    <a class="btn btn-primary" href="{{ route('cvs.create') }}" aria-label="Créer un CV"><i class="bi bi-plus-lg d-sm-none" aria-hidden="true"></i><span class="d-none d-sm-inline">Créer un CV</span></a>
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
