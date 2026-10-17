<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('document-title')@yield('document-title') — CVora @else Connexion — CVora @endif</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700&display=swap" rel="stylesheet">
    @include('partials.assets')
</head>
<body class="overflow-x-hidden bg-mist text-ink antialiased">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6" aria-label="Retour à l'accueil">@include('components.logo')</a>
        <div class="card p-6 sm:p-8">
            @include('components.flash')
            @yield('content')
        </div>
    </main>
</body>
</html>
