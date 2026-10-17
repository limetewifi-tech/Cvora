@extends('layouts.admin')
@section('document-title', 'Offres')
@section('content')
    <h1 class="text-3xl font-semibold">Offres et quotas</h1>
    @foreach ($plans as $plan)
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="card mt-4 grid gap-3 p-4 md:grid-cols-3">
            @csrf @method('PUT')
            <input class="field" name="name" value="{{ $plan->name }}">
            <input class="field" name="slug" value="{{ $plan->slug }}">
            <input class="field" name="price" value="{{ $plan->price }}">
            <input class="field" name="currency" value="{{ $plan->currency }}" maxlength="3">
            <select class="field" name="interval"><option value="month" @selected($plan->interval === 'month')>Mois</option><option value="year" @selected($plan->interval === 'year')>An</option></select>
            <input class="field" name="ai_credits" value="{{ $plan->ai_credits }}">
            <input class="field" name="resume_limit" value="{{ $plan->resume_limit }}" placeholder="CV, vide = illimité">
            <input class="field" name="letter_limit" value="{{ $plan->letter_limit }}" placeholder="Lettres, vide = illimité">
            <input class="field" name="sort_order" value="{{ $plan->sort_order }}">
            <textarea class="field md:col-span-3" name="description">{{ $plan->description }}</textarea>
            @foreach (['export_pdf' => 'PDF', 'export_docx' => 'Word', 'premium_templates' => 'Modèles premium', 'version_history' => 'Historique', 'job_adaptation' => 'Adaptation offre', 'resume_refresh' => 'Actualisation', 'cover_letters' => 'Lettres'] as $feature => $label)
                <label class="text-sm"><input type="checkbox" name="features[{{ $feature }}]" value="1" @checked($plan->allows($feature))> {{ $label }}</label>
            @endforeach
            <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked($plan->is_active)> Active</label>
            <button class="btn btn-primary md:col-span-3" type="submit">Enregistrer {{ $plan->name }}</button>
        </form>
    @endforeach
@endsection
