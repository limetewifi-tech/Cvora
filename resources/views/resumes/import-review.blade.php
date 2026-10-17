@extends('layouts.app')
@section('document-title', 'Vérifier l\'import')
@section('content')
    <h1 class="text-3xl font-semibold">Vérifier les informations détectées</h1>
    @foreach ($import->warnings ?? [] as $warning)
        <p class="mt-3 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $warning }}</p>
    @endforeach
    @if ($import->path)
        <p class="mt-3 text-sm"><a class="font-semibold text-brand-700" href="{{ route('imports.file', $import) }}">Télécharger le fichier original</a></p>
    @endif
    <p class="mt-3 text-sm text-slate-600">Champs repérés : {{ $detected ? implode(', ', $detected) : 'aucun champ fiable' }}.</p>
    <form method="POST" action="{{ route('imports.confirm', $import) }}" class="card mt-6 space-y-4 p-6">
        @csrf
        <div><label class="label" for="title">Titre du nouveau document</label><input class="field" id="title" name="title" value="{{ old('title', 'CV importé') }}" required></div>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'headline' => 'Titre', 'email' => 'E-mail', 'phone' => 'Téléphone', 'city' => 'Ville', 'country' => 'Pays'] as $key => $label)
                <div><label class="label">{{ $label }}</label><input class="field" name="personal[{{ $key }}]" value="{{ $personal[$key] ?? '' }}"></div>
            @endforeach
        </div>
        <div><label class="label">Résumé</label><textarea class="field min-h-28" name="summary">{{ $summary }}</textarea></div>
        @foreach (\App\Support\ResumeContent::sections() as $section)
            <fieldset class="rounded-2xl border border-slate-200 p-4">
                <legend class="px-1 font-semibold">{{ \App\Support\ResumeContent::labels()[$section]['fr'] }}</legend>
                @forelse ($content[$section] ?? [] as $index => $item)
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <input type="hidden" name="{{ $section }}[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                        @foreach (\App\Support\ResumeContent::fields($section) as $field)
                            @if ($field['type'] !== 'checkbox')
                                <input class="field" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="{{ $item[$field['name']] ?? '' }}" placeholder="{{ $field['label'] }}">
                            @endif
                        @endforeach
                    </div>
                @empty
                    <p class="mt-2 text-sm text-slate-500">Rien de détecté. Vous pourrez compléter dans l'éditeur.</p>
                @endforelse
            </fieldset>
        @endforeach
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirmed" value="1" required> Je confirme ou je corrige ces informations avant l'enregistrement.</label>
        <div>
            <label class="label" for="resume_id">Mettre à jour un CV existant (facultatif)</label>
            <select class="field" id="resume_id" name="resume_id"><option value="">Créer un nouveau CV</option>@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select>
        </div>
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="overwrite_confirmed" value="1"> Je confirme le remplacement du CV sélectionné. Sans cette case, rien n'est écrasé.</label>
        <button class="btn btn-primary" type="submit">Enregistrer les données validées</button>
    </form>
@endsection
