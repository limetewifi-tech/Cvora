@extends('layouts.public')
@section('document-title', 'CVora — Vos candidatures, plus simples')
@section('content')
    <section class="container py-4 py-lg-5">
        <h1 class="cvora-home-title mb-3">Vos candidatures, plus simples avec CVora.</h1>
        <p class="lead text-secondary mb-4 mb-lg-5">Créez votre CV, améliorez votre parcours avec l'IA et rédigez vos lettres en quelques minutes.</p>

        <div class="row g-3 g-lg-4">
            <div class="col-lg-4">
                <article class="cvora-action">
                    <span class="cvora-action-icon" aria-hidden="true"><i class="bi bi-file-earmark-plus"></i></span>
                    <h2 class="h4 mt-3">Créer un CV</h2>
                    <p class="text-secondary">Choisissez un modèle professionnel et créez un CV adapté à votre parcours.</p>
                    <a class="btn btn-primary mt-auto" href="{{ route('cvs.create') }}">Créer mon CV</a>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="cvora-action">
                    <span class="cvora-action-icon" aria-hidden="true"><i class="bi bi-stars"></i></span>
                    <h2 class="h4 mt-3">Améliorer mon CV avec l'IA</h2>
                    <p class="text-secondary">Importez votre CV existant et améliorez sa présentation ainsi que son contenu avec l'aide de l'IA.</p>
                    <a class="btn btn-primary mt-auto" href="{{ route('imports.create') }}">Améliorer mon CV</a>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="cvora-action">
                    <span class="cvora-action-icon" aria-hidden="true"><i class="bi bi-envelope-paper"></i></span>
                    <h2 class="h4 mt-3">Créer une lettre avec l'IA</h2>
                    <p class="text-secondary">Générez une lettre de motivation personnalisée pour le poste que vous souhaitez obtenir.</p>
                    <a class="btn btn-primary mt-auto" href="{{ route('letters.create') }}">Générer ma lettre</a>
                </article>
            </div>
        </div>

        <div class="mt-4 mt-lg-5">
            @if ($documents->isNotEmpty())
                <h2 class="h5 mb-3">Vos derniers documents</h2>
                <ul class="list-unstyled row g-2 mb-0">
                    @foreach ($documents as $document)
                        <li class="col-md-6">
                            <a class="cvora-doc d-flex align-items-center justify-content-between gap-3" href="{{ $document['href'] }}">
                                <span>
                                    <span class="small text-secondary">{{ $document['kind'] }}</span>
                                    <span class="d-block fw-semibold">{{ $document['title'] }}</span>
                                </span>
                                <span class="small text-secondary text-nowrap">{{ $document['updated_at']?->translatedFormat('j M Y') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="cvora-welcome mb-0">Bienvenue. Commencez par l'une de ces trois actions.</p>
            @endif
        </div>
    </section>
@endsection
