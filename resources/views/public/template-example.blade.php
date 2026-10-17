@extends('layouts.public')
@section('document-title', $template->name.' — modèle CVora')
@section('content')
    <div class="container cvora-sheet py-5">
        <p class="alert alert-warning">Cet aperçu utilise des données fictives pour illustrer la mise en page « {{ $template->name }} ». Ce n'est pas un témoignage.</p>
        <h1 class="h2 fw-semibold">{{ $template->name }}</h1>
        <p class="text-secondary">{{ $template->description }}</p>
        <iframe title="Exemple {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="cvora-frame-paper w-100 rounded border bg-white mt-3"></iframe>
        <a class="btn btn-primary mt-4" href="{{ route('register') }}">Utiliser ce modèle</a>
    </div>
@endsection
