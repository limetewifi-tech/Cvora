@extends('layouts.auth')
@section('document-title', 'Connexion')
@section('content')
    <h1 class="text-2xl font-semibold">Connexion</h1>
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
        <div><label class="label" for="password">Mot de passe</label><input class="field" id="password" type="password" name="password" required></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Se souvenir de moi</label>
        <button class="btn btn-primary w-full" type="submit">Se connecter</button>
    </form>
    <p class="mt-4 text-sm"><a class="font-semibold text-brand-700" href="{{ route('password.request') }}">Mot de passe oublié</a></p>
    <p class="mt-2 text-sm">Pas encore de compte ? <a class="font-semibold text-brand-700" href="{{ route('register') }}">Créer un compte</a></p>
@endsection
