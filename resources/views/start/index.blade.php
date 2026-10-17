@extends('layouts.app')
@section('document-title', 'Commencer')
@section('content')
    <h1 class="h2">Commencer</h1>
    <p class="text-secondary">Choisissez un objectif. Vous fournissez les faits, l'assistant reformule, et vous validez le résultat.</p>
    <ol class="list-inline small text-secondary mb-4">
        <li class="list-inline-item">1. Objectif</li>
        <li class="list-inline-item">2. Informations</li>
        <li class="list-inline-item">3. Reformulation</li>
        <li class="list-inline-item">4. Aperçu A4 et PDF</li>
    </ol>
    @if (! $goal)
        <div class="row g-3">
            <div class="col-md-4">
                <a class="card h-100 text-decoration-none" href="{{ route('start.index', ['objectif' => 'creer']) }}">
                    <div class="card-body">
                        <h2 class="h5">Créer un CV</h2>
                        <p class="text-secondary mb-0">Partir du profil et ouvrir l'aperçu A4.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="card h-100 text-decoration-none" href="{{ route('start.index', ['objectif' => 'ameliorer']) }}">
                    <div class="card-body">
                        <h2 class="h5">Améliorer un CV</h2>
                        <p class="text-secondary mb-0">Continuer un document ou en importer un.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="card h-100 text-decoration-none" href="{{ route('start.index', ['objectif' => 'offre']) }}">
                    <div class="card-body">
                        <h2 class="h5">Répondre à une offre</h2>
                        <p class="text-secondary mb-0">Comparer un CV au texte d'une annonce.</p>
                    </div>
                </a>
            </div>
        </div>
    @elseif ($goal === 'creer')
        <form method="POST" action="{{ route('start.store') }}" class="card cvora-narrow needs-validation" novalidate>
            @csrf
            <div class="card-body vstack gap-3">
                <h2 class="h5">Créer un CV</h2>
                <p class="small text-secondary mb-0">Le modèle retenu dans vos paramètres est utilisé. Vous pourrez en changer dans l'éditeur, à côté de l'aperçu.</p>
                <div>
                    <label class="form-label" for="title">Titre du document</label>
                    <input class="form-control" id="title" name="title" value="{{ old('title', $defaultTitle) }}" required>
                    <div class="invalid-feedback">Donnez un titre au document.</div>
                </div>
                <div>
                    <label class="form-label" for="headline">Poste visé</label>
                    <input class="form-control" id="headline" name="headline" value="{{ old('headline', $defaultHeadline) }}" maxlength="160" placeholder="Exemple : développeur Laravel">
                </div>
                <button class="btn btn-primary" type="submit" data-loading>Créer et ouvrir l'aperçu</button>
                <a href="{{ route('cvs.create') }}">Choisir un autre modèle avant de créer</a>
            </div>
        </form>
    @elseif ($goal === 'ameliorer')
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a class="btn btn-outline-primary" href="{{ route('imports.create') }}">Importer un CV</a>
            <a class="btn btn-outline-primary" href="{{ route('start.index', ['objectif' => 'creer']) }}">Créer un nouveau CV</a>
        </div>
        @forelse ($resumes as $resume)
            <article class="card mb-3">
                <div class="card-body d-flex flex-column flex-sm-row justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $resume->title }}</h2>
                        <p class="small text-secondary mb-0">{{ $resume->template?->name }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-primary" href="{{ route('cvs.edit', $resume) }}">Continuer</a>
                        <a class="btn btn-primary" href="{{ route('assistant.index', ['cv' => $resume->id]) }}">Demander à l'assistant</a>
                    </div>
                </div>
            </article>
        @empty
            @include('components.empty', ['icon' => 'bi-file-earmark', 'title' => 'Aucun CV à améliorer.', 'action' => 'Créer un CV', 'href' => route('start.index', ['objectif' => 'creer'])])
        @endforelse
    @else
        @if ($resumes->isEmpty())
            @include('components.empty', ['icon' => 'bi-file-earmark-text', 'title' => 'Enregistrez d\'abord un CV.', 'action' => 'Créer un CV', 'href' => route('start.index', ['objectif' => 'creer'])])
        @else
            <form method="POST" action="{{ route('studio.store') }}" class="card needs-validation" novalidate data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Répondre à une offre</h2>
                    <p class="small text-secondary mb-0">Le score est calculé ici, sans inventer de compétence. Le CV ne change qu'après confirmation dans le résultat.</p>
                    <div>
                        <label class="form-label" for="resume_id">CV</label>
                        <select class="form-select" id="resume_id" name="resume_id" required>
                            @foreach ($resumes as $resume)
                                <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="offer_title">Intitulé du poste</label>
                        <input class="form-control" id="offer_title" name="offer_title" value="{{ old('offer_title') }}" required maxlength="160">
                    </div>
                    <div>
                        <label class="form-label" for="company">Organisation</label>
                        <input class="form-control" id="company" name="company" value="{{ old('company') }}" maxlength="160">
                    </div>
                    <div>
                        <label class="form-label" for="offer_text">Texte de l'offre</label>
                        <textarea class="form-control cvora-textarea" id="offer_text" name="offer_text" required>{{ old('offer_text') }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_resume" value="1" required>
                        <label class="form-check-label" for="authorize_resume">J'autorise l'utilisation de ce CV pour cette comparaison.</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="use_ai" id="use_ai" value="1" @disabled(! $canAdapt)>
                        <label class="form-check-label" for="use_ai">Demander aussi une suggestion au fournisseur{{ $canAdapt ? '' : ' (offre Pro)' }}</label>
                    </div>
                    <button class="btn btn-primary" type="submit" data-loading>Comparer</button>
                </div>
            </form>
        @endif
    @endif
@endsection