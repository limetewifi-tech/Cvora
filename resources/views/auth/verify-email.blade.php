@extends('layouts.auth')
@section('document-title', 'Vérifier l\'e-mail')
@section('content')
    <h1 class="h3">Vérifiez votre e-mail</h1>
    <p class="text-secondary small">Un lien de vérification a été envoyé. L'accès à l'espace personnel reste bloqué tant que l'adresse n'est pas confirmée.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
        @csrf
        <button class="btn btn-primary" type="submit" data-loading>Renvoyer le lien</button>
    </form>
@endsection
