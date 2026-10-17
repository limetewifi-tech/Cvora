@extends('layouts.auth')
@section('document-title', 'Connexion')
@section('content')
    <h1 class="h3">Connexion</h1>
    <form method="POST" action="{{ route('login') }}" class="vstack gap-3 mt-4 needs-validation" novalidate>
        @csrf
        <div>
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <div class="invalid-feedback">Indiquez l'e-mail du compte.</div>
        </div>
        <div>
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-control" id="password" type="password" name="password" required autocomplete="current-password">
            <div class="invalid-feedback">Indiquez votre mot de passe.</div>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <button class="btn btn-primary" type="submit" data-loading>Se connecter</button>
    </form>
    <p class="mt-4 mb-2 small"><a class="fw-semibold" href="{{ route('password.request') }}">Mot de passe oublié</a></p>
    <p class="mb-0 small">Pas encore de compte ? <a class="fw-semibold" href="{{ route('register') }}">Créer un compte</a></p>
@endsection
