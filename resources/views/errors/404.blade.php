@extends('layouts.public')
@section('document-title', 'Page introuvable')
@section('content')
    <div class="container cvora-center py-5 text-center">
        <i class="bi bi-signpost display-4 text-primary" aria-hidden="true"></i>
        <h1 class="h2 mt-3">Page introuvable</h1>
        <a class="btn btn-primary mt-3" href="{{ route('home') }}">Retour à l'accueil</a>
    </div>
@endsection
