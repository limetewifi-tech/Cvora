@extends('layouts.auth')
@section('document-title', 'Mot de passe oublié')
@section('content')
    <h1 class="h3">Réinitialiser le mot de passe</h1>
    <p class="text-secondary small">Indiquez votre e-mail. Si un compte existe, un lien vous sera envoyé.</p>
    <form method="POST" action="{{ route('password.email') }}" class="vstack gap-3 mt-4 needs-validation" novalidate>
        @csrf
        <div>
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            <div class="invalid-feedback">Indiquez une adresse e-mail valide.</div>
        </div>
        <button class="btn btn-primary" type="submit" data-loading>Envoyer le lien</button>
    </form>
@endsection
