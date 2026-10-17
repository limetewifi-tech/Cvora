@extends('layouts.auth')
@section('document-title', 'Nouveau mot de passe')
@section('content')
    <h1 class="h3">Nouveau mot de passe</h1>
    <form method="POST" action="{{ route('password.update') }}" class="vstack gap-3 mt-4 needs-validation" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            <div class="invalid-feedback">Indiquez une adresse e-mail valide.</div>
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
        <button class="btn btn-primary" type="submit" data-loading>Enregistrer</button>
    </form>
@endsection
