@extends('layouts.public')
@section('document-title', 'Modèles de CV — CVora')
@section('content')
    <div class="container py-5">
        <p class="cvora-kicker mb-2">Modèles</p>
        <h1 class="display-6 fw-semibold">Une galerie, huit mises en page.</h1>
        <p class="text-secondary cvora-narrow mt-3">Chaque aperçu est le vrai modèle, avec des données fictives. Choisir un modèle ne change que la présentation : vos expériences, formations et compétences restent dans le CV.</p>
        <div class="mt-4">
            @include('public.partials.gallery', ['templates' => $templates])
        </div>
    </div>
@endsection
