<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    <title>@hasSection('document-title')@yield('document-title') — CVora @else Connexion — CVora @endif</title>
</head>
<body>
    <main class="container cvora-auth d-flex min-vh-100 flex-column justify-content-center py-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="{{ route('home') }}" aria-label="Retour à l'accueil">@include('components.logo')</a>
            @include('partials.theme-toggle')
        </div>
        <div class="card">
            <div class="card-body p-4 p-sm-5">
                @include('components.flash')
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
