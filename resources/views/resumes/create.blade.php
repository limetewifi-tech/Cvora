@extends('layouts.app')
@section('document-title', 'Créer un CV')
@section('content')
    <h1 class="text-3xl font-semibold">Créer un CV</h1>
    <p class="mt-2 text-slate-600">Le profil complet les champs connus. Vous pourrez tout corriger ensuite.</p>
    <form method="POST" action="{{ route('cvs.store') }}" class="card mt-6 max-w-xl space-y-4 p-6">
        @csrf
        <div><label class="label" for="title">Titre du document</label><input class="field" id="title" name="title" value="{{ old('title', 'Mon CV') }}" required></div>
        <fieldset>
            <legend class="label">Modèle</legend>
            <div class="space-y-2">
                @foreach ($templates as $template)
                    @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 {{ $allowed ? '' : 'opacity-60' }}">
                        <input type="radio" name="template_id" value="{{ $template->id }}" @checked(old('template_id', $templates->firstWhere('is_premium', false)?->id) == $template->id) @disabled(! $allowed)>
                        <span><span class="font-semibold">{{ $template->name }}</span><span class="block text-sm text-slate-600">{{ $template->description }}</span></span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <button class="btn btn-primary" type="submit">Continuer dans l'éditeur</button>
    </form>
@endsection
