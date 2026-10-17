@extends('layouts.app')
@section('document-title', 'Créer un CV')
@section('content')
    <h1 class="h2">Créer un CV</h1>
    <p class="text-secondary">Le profil complète les champs connus. Vous pourrez tout corriger ensuite.</p>
    <form method="POST" action="{{ route('cvs.store') }}" class="card cvora-narrow mt-4 needs-validation" novalidate>
        @csrf
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label" for="title">Titre du document</label>
                <input class="form-control" id="title" name="title" value="{{ old('title', 'Mon CV') }}" required>
                <div class="invalid-feedback">Donnez un titre au document.</div>
            </div>
            <fieldset>
                <legend class="form-label mb-1">Choisissez un modèle</legend>
                <p class="small text-secondary">La mise en page du CV. Vous pourrez en changer dans l'éditeur.</p>
                @php
                    $defaultTemplate = $templates->first(fn ($template) => ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'));
                @endphp
                @if ($templates->isEmpty())
                    <p class="alert alert-warning mb-0">Aucun modèle n'est disponible pour le moment.</p>
                @else
                    <div class="vstack gap-2">
                        @foreach ($templates as $template)
                            @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                            <label class="cvora-choice d-flex gap-3 border rounded p-3 {{ $allowed ? '' : 'opacity-75' }}">
                                <input class="form-check-input mt-1" type="radio" name="template_id" value="{{ $template->id }}" @checked(old('template_id', $defaultTemplate?->id) == $template->id) @disabled(! $allowed) @required($allowed)>
                                <span>
                                    <span class="fw-semibold">{{ $template->name }}</span>
                                    <span class="badge text-bg-{{ $template->is_premium ? 'warning' : 'primary' }} ms-1">{{ $template->is_premium ? 'Premium' : 'Inclus' }}</span>
                                    <span class="d-block small text-secondary">{{ $template->description }}</span>
                                    @unless ($allowed)
                                        <span class="d-block small">Disponible avec une offre qui inclut les modèles premium.</span>
                                    @endunless
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                @error('template_id')
                    <p class="text-danger small mb-0 mt-2">{{ $message }}</p>
                @enderror
            </fieldset>
            <button class="btn btn-primary" type="submit" data-loading>Continuer dans l'éditeur</button>
        </div>
    </form>
@endsection
