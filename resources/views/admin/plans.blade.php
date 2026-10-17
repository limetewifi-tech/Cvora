@extends('layouts.admin')
@section('document-title', 'Offres')
@section('content')
    <h1 class="h2">Offres et quotas</h1>
    @foreach ($plans as $plan)
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="card mt-3">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ $plan->name }}"></div>
                    <div class="col-md-4"><label class="form-label">Identifiant</label><input class="form-control" name="slug" value="{{ $plan->slug }}"></div>
                    <div class="col-md-4"><label class="form-label">Prix</label><input class="form-control" name="price" value="{{ $plan->price }}"></div>
                    <div class="col-md-4"><label class="form-label">Devise</label><input class="form-control" name="currency" value="{{ $plan->currency }}" maxlength="3"></div>
                    <div class="col-md-4">
                        <label class="form-label">Période</label>
                        <select class="form-select" name="interval">
                            <option value="month" @selected($plan->interval === 'month')>Mois</option>
                            <option value="year" @selected($plan->interval === 'year')>An</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Crédits IA</label><input class="form-control" name="ai_credits" value="{{ $plan->ai_credits }}"></div>
                    <div class="col-md-4"><label class="form-label">Limite de CV</label><input class="form-control" name="resume_limit" value="{{ $plan->resume_limit }}" placeholder="Vide = illimité"></div>
                    <div class="col-md-4"><label class="form-label">Limite de lettres</label><input class="form-control" name="letter_limit" value="{{ $plan->letter_limit }}" placeholder="Vide = illimité"></div>
                    <div class="col-md-4"><label class="form-label">Ordre</label><input class="form-control" name="sort_order" value="{{ $plan->sort_order }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ $plan->description }}</textarea></div>
                    @foreach (['export_pdf' => 'PDF', 'export_docx' => 'Word', 'premium_templates' => 'Modèles premium', 'version_history' => 'Historique', 'job_adaptation' => 'Adaptation offre', 'resume_refresh' => 'Actualisation', 'cover_letters' => 'Lettres'] as $feature => $label)
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[{{ $feature }}]" id="feature-{{ $plan->id }}-{{ $feature }}" value="1" @checked($plan->allows($feature))>
                                <label class="form-check-label" for="feature-{{ $plan->id }}-{{ $feature }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="plan-active-{{ $plan->id }}" value="1" @checked($plan->is_active)>
                            <label class="form-check-label" for="plan-active-{{ $plan->id }}">Active</label>
                        </div>
                    </div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">Enregistrer {{ $plan->name }}</button></div>
                </div>
            </div>
        </form>
    @endforeach
@endsection
