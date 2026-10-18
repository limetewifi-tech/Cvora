<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    <meta name="description" content="CVora aide à créer, améliorer et actualiser un CV et une lettre de motivation. Vos informations restent les vôtres.">
    <title>@hasSection('document-title')@yield('document-title')@else CVora — Votre talent mérite le meilleur CV. @endif</title>
</head>
<body class="cvora-public">
    <a href="#contenu" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-light">Aller au contenu</a>
    <header class="cvora-navbar sticky-top">
        <nav class="navbar navbar-expand-lg" aria-label="Navigation principale">
            <div class="container">
                <a class="navbar-brand py-0" href="{{ route('home') }}" aria-label="CVora, accueil">@include('components.logo')</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-public" aria-controls="menu-public" aria-expanded="false" aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menu-public">
                    <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1 py-3 py-lg-0">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">Modèles</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Tarifs</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('resources') || request()->routeIs('resources.show') ? 'active' : '' }}" href="{{ route('resources') }}">Guides</a></li>
                    </ul>
                    <div class="d-grid gap-2 d-lg-flex align-items-lg-center ms-lg-3 pb-3 pb-lg-0">
                        @include('partials.theme-toggle')
                        @auth
                            <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">Tableau de bord</a>
                        @else
                            <a class="btn btn-outline-primary" href="{{ route('login') }}">Connexion</a>
                            <a class="btn btn-primary" href="{{ route('templates.index') }}">Créer un CV</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main id="contenu">
        @yield('content')
    </main>
    <footer class="cvora-footer border-top">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-sm-6 col-lg-3">
                    @include('components.logo')
                    <p class="mt-3 mb-0 small text-secondary">L'intelligence au service de votre carrière. Créez, améliorez et actualisez vos candidatures.</p>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <h2 class="h6">Produit</h2>
                    <ul class="list-unstyled small text-secondary mt-3">
                        <li class="mb-2"><a href="{{ route('features') }}">Fonctionnalités</a></li>
                        <li class="mb-2"><a href="{{ route('templates.index') }}">Modèles</a></li>
                        <li><a href="{{ route('pricing') }}">Tarifs</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <h2 class="h6">Ressources</h2>
                    <ul class="list-unstyled small text-secondary mt-3">
                        <li class="mb-2"><a href="{{ route('resources') }}">Guides</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <h2 class="h6">Informations</h2>
                    <ul class="list-unstyled small text-secondary mt-3">
                        <li class="mb-2"><a href="{{ route('legal.privacy') }}">Confidentialité</a></li>
                        <li class="mb-2"><a href="{{ route('legal.terms') }}">Conditions d'utilisation</a></li>
                        <li><a href="{{ route('legal.notice') }}">Mentions légales</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <p class="border-top mb-0 px-3 py-3 text-center small text-secondary">© {{ date('Y') }} CVora. Aucune promesse d'embauche.</p>
    </footer>
</body>
</html>
