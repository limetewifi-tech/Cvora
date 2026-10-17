@extends('layouts.auth')
@section('document-title', 'Inscription')
@section('content')
    <h1 class="h3">Créer mon compte</h1>
    <form method="POST" action="{{ route('register') }}" class="vstack gap-3 mt-4 needs-validation" novalidate>
        @csrf
        <div>
            <label class="form-label" for="name">Nom complet</label>
            <input class="form-control" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
            <div class="invalid-feedback">Indiquez votre nom.</div>
        </div>
        <div>
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            <div class="invalid-feedback">Indiquez une adresse e-mail valide.</div>
        </div>
        <div>
            <label class="form-label" for="phone">Téléphone <span class="fw-normal text-secondary">(facultatif, pour une future connexion)</span></label>
            <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
        </div>
        <div>
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-control" id="password" type="password" name="password" required autocomplete="new-password">
            <div class="invalid-feedback">Choisissez un mot de passe.</div>
        </div>
        <div>
            <label class="form-label" for="password_confirmation">Confirmation</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            <div class="invalid-feedback">Confirmez le mot de passe.</div>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="accepted_terms" id="accepted_terms" value="1" required>
            <label class="form-check-label" for="accepted_terms">J'accepte les <a class="fw-semibold" href="{{ route('legal.terms') }}">conditions</a> et la <a class="fw-semibold" href="{{ route('legal.privacy') }}">confidentialité</a>.</label>
            <div class="invalid-feedback">Acceptez les conditions pour créer le compte.</div>
        </div>
        <button class="btn btn-primary" type="submit" data-loading>Créer mon CV gratuitement</button>
    </form>
@endsection
