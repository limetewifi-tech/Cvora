<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('document-title')@yield('document-title') — CVora @else Éditeur CVora @endif</title>
</head>
<body class="cvora-studio">
    <a href="#contenu" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-light">Aller au contenu</a>
    <header class="cvora-studio-bar">
        <a href="{{ route('templates.index') }}" aria-label="Galerie des modèles">@include('components.logo')</a>
        <nav class="d-none d-md-flex align-items-center gap-3 small" aria-label="Parcours">
            <a href="{{ route('templates.index') }}">Modèles</a>
            <a href="{{ route('cvs.index') }}">Mes CV</a>
            <a href="{{ route('letters.index') }}">Lettres</a>
            <a href="{{ route('assistant.index') }}">Assistant</a>
        </nav>
        <div class="d-flex align-items-center gap-2 ms-auto">
            @include('partials.theme-toggle')
            <a class="btn btn-outline-primary btn-sm" href="{{ route('cvs.index') }}">Mes CV</a>
        </div>
    </header>
    <main id="contenu" class="cvora-studio-main">
        @include('components.flash')
        @yield('content')
    </main>
</body>
</html>
