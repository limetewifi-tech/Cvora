<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('document-title')@yield('document-title') — Administration @else Administration CVora @endif</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700&display=swap" rel="stylesheet">
    @include('partials.assets')
</head>
<body class="overflow-x-hidden bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
        <aside class="bg-slate-900 p-4">
            <a href="{{ route('admin.index') }}" class="block px-2 py-2" aria-label="Administration CVora">@include('components.logo', ['variant' => 'light'])</a>
            <nav class="mt-6 space-y-1 text-sm" aria-label="Administration">
                @foreach ([
                    'admin.index' => 'Vue d\'ensemble',
                    'admin.users' => 'Comptes',
                    'admin.plans' => 'Offres et tarifs',
                    'admin.templates' => 'Modèles',
                    'admin.transactions' => 'Transactions',
                    'admin.subscriptions' => 'Abonnements',
                    'admin.ai' => 'Quotas IA',
                    'admin.settings' => 'Configuration',
                    'admin.errors' => 'Journaux',
                ] as $route => $label)
                    <a href="{{ route($route) }}" class="block rounded-xl px-3 py-2 {{ request()->routeIs($route) ? 'bg-white text-slate-900' : 'text-slate-200 hover:bg-slate-800' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <a href="{{ route('dashboard') }}" class="mt-6 block px-3 text-sm text-slate-300">Retour à l'espace personnel</a>
        </aside>
        <main class="min-w-0 bg-mist text-ink">
            <div class="mx-auto max-w-6xl px-4 py-6">
                @include('components.flash')
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
