@extends('layouts.public')
@section('document-title', 'Modèles de lettres — CVora')
@section('content')
    <div class="container py-5">
        <p class="cvora-kicker mb-2">Lettres</p>
        <h1 class="display-6 fw-semibold">Une lettre adaptée à la candidature.</h1>
        <p class="text-secondary cvora-narrow mt-3">Chaque aperçu est le vrai modèle A4, avec un texte fictif. Choisir un modèle ne rédige pas la lettre à votre place.</p>
        <div class="mt-4">
            @include('public.partials.letter-gallery')
        </div>
    </div>
@endsection
