@extends('layouts.app')
@section('document-title', 'Créer un CV')
@section('content')
    <h1 class="h2">Créer un CV</h1>
    <p class="text-secondary">Le profil complet les champs connus. Vous pourrez tout corriger ensuite.</p>
    <form method="POST" action="{{ route('cvs.store') }}" class="card cvora-narrow mt-4 needs-validation" novalidate>
        @csrf
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label" for="title">Titre du document</label>
                <input class="form-control" id="title" name="title" value="{{ old('title', 'Mon CV') }}" required>
                <div class="invalid-feedback">Donnez un titre au document.</div>
            </div>
            <fieldset>
                <legend class="form-label">Modèle</legend>
                <div class="vstack gap-2">
                    @foreach ($templates as $template)
                        @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                        <label class="d-flex gap-3 border rounded p-3 {{ $allowed ? '' : 'opacity-50' }}">
                            <input class="form-check-input mt-1" type="radio" name="template_id" value="{{ $template->id }}" @checked(old('template_id', $templates->firstWhere('is_premium', false)?->id) == $template->id) @disabled(! $allowed)>
                            <span><span class="fw-semibold">{{ $template->name }}</span><span class="d-block small text-secondary">{{ $template->description }}</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <button class="btn btn-primary" type="submit" data-loading>Continuer dans l'éditeur</button>
        </div>
    </form>
@endsection
