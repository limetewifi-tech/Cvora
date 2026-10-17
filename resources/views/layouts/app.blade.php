<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('document-title')@yield('document-title') — CVora @else Espace CVora @endif</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700&display=swap" rel="stylesheet">
    @include('partials.assets')
</head>
<body class="overflow-x-hidden bg-mist text-ink antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2">Aller au contenu</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside id="sidebar" class="border-b border-slate-200 bg-white lg:block lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between px-4 py-4">
                <a href="{{ route('dashboard') }}" aria-label="Tableau de bord CVora">@include('components.logo')</a>
            </div>
            <nav class="space-y-1 px-3 pb-6 text-sm" aria-label="Espace personnel">
                @php
                    $links = [
                        ['dashboard', 'Vue d\'ensemble', 'dashboard'],
                        ['cvs.index', 'Mes CV', 'cvs.*'],
                        ['cvs.create', 'Créer un CV', 'cvs.create'],
                        ['imports.create', 'Importer un CV', 'imports.*'],
                        ['letters.index', 'Lettres de motivation', 'letters.*'],
                        ['assistant.index', 'Assistant IA', 'assistant.*'],
                        ['applications.index', 'Mes candidatures', 'applications.*'],
                        ['jobs.index', 'Offres d\'emploi', 'jobs.*'],
                        ['templates.index', 'Modèles', 'templates.*'],
                        ['profile.edit', 'Mon profil', 'profile.*'],
                        ['billing.index', 'Abonnement et facturation', 'billing.*'],
                        ['settings.edit', 'Paramètres', 'settings.*'],
                    ];
                @endphp
                @foreach ($links as [$route, $label, $pattern])
                    <a href="{{ route($route) }}" class="block rounded-xl px-3 py-2 font-medium {{ request()->routeIs($pattern) ? 'bg-brand-100 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">{{ $label }}</a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}" class="pt-3">
                    @csrf
                    <button class="btn btn-ghost w-full justify-start" type="submit">Déconnexion</button>
                </form>
            </nav>
        </aside>
        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur">
                <button type="button" class="btn btn-secondary lg:hidden" data-toggle="#sidebar" aria-expanded="false" aria-controls="sidebar">Navigation</button>
                <p class="truncate text-sm text-slate-600">{{ auth()->user()->name }}</p>
                <a class="btn btn-primary" href="{{ route('cvs.create') }}">Créer un CV</a>
            </header>
            <main id="contenu" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
                @include('components.flash')
                @yield('content')
            </main>
        </div>
    </div>
    <style>
        #sidebar { display: none; }
        #sidebar.is-open { display: block; }
        @media (min-width: 1024px) {
            #sidebar { display: block !important; }
        }
    </style>
</body>
</html>
