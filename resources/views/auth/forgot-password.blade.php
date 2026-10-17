@extends('layouts.auth')
@section('document-title', 'Mot de passe oublié')
@section('content')
    <h1 class="text-2xl font-semibold">Réinitialiser le mot de passe</h1>
    <p class="mt-2 text-sm text-slate-600">Indiquez votre e-mail. Si un compte existe, un lien vous sera envoyé.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
        <button class="btn btn-primary w-full" type="submit">Envoyer le lien</button>
    </form>
@endsection
