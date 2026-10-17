@extends('layouts.auth')
@section('document-title', 'Vérifier l\'e-mail')
@section('content')
    <h1 class="text-2xl font-semibold">Vérifiez votre e-mail</h1>
    <p class="mt-2 text-sm text-slate-600">Un lien de vérification a été envoyé. L'accès à l'espace personnel reste bloqué tant que l'adresse n'est pas confirmée.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button class="btn btn-primary" type="submit">Renvoyer le lien</button>
    </form>
@endsection
