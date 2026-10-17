<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CVora aide à créer, améliorer et actualiser un CV et une lettre de motivation. Vos informations restent les vôtres.">
    <title>@hasSection('document-title')@yield('document-title')@else CVora — Votre talent mérite le meilleur CV. @endif</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700&display=swap" rel="stylesheet">
    @include('partials.assets')
</head>
<body class="overflow-x-hidden bg-white text-ink antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2">Aller au contenu</a>
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('home') }}" aria-label="CVora, accueil">@include('components.logo')</a>
            <button type="button" class="btn btn-secondary lg:hidden" data-toggle="#menu-public" aria-expanded="false" aria-controls="menu-public">Menu</button>
            <nav id="menu-public" class="absolute inset-x-4 top-16 flex-col gap-1 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg lg:static lg:flex lg:flex-row lg:items-center lg:gap-1 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
                <a class="nav-link block" href="{{ route('features') }}">Fonctionnalités</a>
                <a class="nav-link block" href="{{ route('templates.index') }}">Modèles</a>
                <a class="nav-link block" href="{{ route('pricing') }}">Tarifs</a>
                <a class="nav-link block" href="{{ route('resources') }}">Ressources</a>
                <div class="mt-2 flex flex-col gap-2 lg:mt-0 lg:ml-3 lg:flex-row">
                    @auth
                        <a class="btn btn-secondary" href="{{ route('dashboard') }}">Tableau de bord</a>
                    @else
                        <a class="btn btn-ghost" href="{{ route('login') }}">Connexion</a>
                        <a class="btn btn-primary" href="{{ route('register') }}">Créer mon CV gratuitement</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>
    <main id="contenu">
        @yield('content')
    </main>
    <footer class="border-t border-slate-200 bg-mist">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                @include('components.logo')
                <p class="mt-3 text-sm text-slate-600">L'intelligence au service de votre carrière. Créez, améliorez et actualisez vos candidatures.</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold">Produit</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a href="{{ route('features') }}">Fonctionnalités</a></li>
                    <li><a href="{{ route('templates.index') }}">Modèles</a></li>
                    <li><a href="{{ route('pricing') }}">Tarifs</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold">Ressources</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a href="{{ route('resources') }}">Guides</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold">Informations</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a href="{{ route('legal.privacy') }}">Confidentialité</a></li>
                    <li><a href="{{ route('legal.terms') }}">Conditions d'utilisation</a></li>
                    <li><a href="{{ route('legal.notice') }}">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <p class="border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-500">© {{ date('Y') }} CVora. Aucune promesse d'embauche.</p>
    </footer>
    <style>
        #menu-public { display: none; }
        #menu-public.is-open { display: flex; }
        @media (min-width: 1024px) {
            #menu-public { display: flex !important; }
        }
    </style>
</body>
</html>
