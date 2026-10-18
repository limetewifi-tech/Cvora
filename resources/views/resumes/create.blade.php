@extends('layouts.app')
@section('document-title', 'Créer un CV')
@section('content')
    <h1 class="h2">Créer un CV</h1>
    <p class="text-secondary">Indiquez un titre, gardez le modèle Moderne ou choisissez-en un autre, puis ouvrez l'éditeur. Le profil est recopié lorsqu'il contient déjà des informations.</p>
    @if ($plan)
        <p class="small text-secondary mb-0">
            Abonnement {{ $plan->name }}
            @if ($plan->resume_limit)
                · {{ $resumeCount }} / {{ $plan->resume_limit }} CV
            @endif
        </p>
    @endif
    @if ($plan && $plan->resume_limit !== null && $resumeCount >= $plan->resume_limit)
        <div class="alert alert-warning mt-3" role="status">
            Votre abonnement {{ $plan->name }} permet {{ $plan->resume_limit }} CV.
            <a class="alert-link" href="{{ route('billing.index') }}">Voir les abonnements</a>
        </div>
    @endif
    <form method="POST" action="{{ route('cvs.store') }}" class="card cvora-narrow mt-4 needs-validation" novalidate>
        @csrf
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label" for="title">Titre du document</label>
                <input class="form-control" id="title" name="title" value="{{ old('title', $defaultTitle) }}" required>
                <div class="invalid-feedback">Donnez un titre au document.</div>
            </div>
            <fieldset>
                <legend class="form-label mb-1">Choisissez un modèle</legend>
                <p class="small text-secondary">Votre modèle retenu est sélectionné lorsqu'il est disponible. Sinon, Moderne est proposé. Vous pourrez changer de mise en page dans l'éditeur.</p>
                @php
                    $preferred = $templates->first(fn ($template) => $template->slug === ($preferredSlug ?? null) && (! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates')));
                    $defaultTemplate = $preferred ?? $templates->first(fn ($template) => ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'));
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
                                    @if ((string) old('template_id', $defaultTemplate?->id) === (string) $template->id)
                                        <span class="badge text-bg-success ms-1">Sélectionné</span>
                                    @endif
                                    <span class="d-block small text-secondary">{{ $template->description }}</span>
                                    @unless ($allowed)
                                        <span class="d-block small">Disponible avec l'abonnement Premium ou Pro.</span>
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
