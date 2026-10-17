@extends('layouts.public')
@section('document-title', 'Page introuvable')
@section('content')
    <div class="mx-auto max-w-xl px-4 py-20 text-center"><h1 class="text-3xl font-semibold">Page introuvable</h1><a class="btn btn-primary mt-6" href="{{ route('home') }}">Retour à l'accueil</a></div>
@endsection
