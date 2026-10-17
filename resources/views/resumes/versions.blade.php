@extends('layouts.app')
@section('document-title', 'Versions')
@section('content')
    <h1 class="text-3xl font-semibold">Versions de « {{ $resume->title }} »</h1>
    @unless ($historyEnabled)
        <p class="mt-3 rounded-2xl bg-brand-50 px-4 py-3 text-sm text-brand-700">Les versions de secours restent disponibles. L'historique manuel complet est inclus dans l'offre Pro.</p>
    @endunless
    @if ($historyEnabled)
        <form method="POST" action="{{ route('cvs.versions.store', $resume) }}" class="card mt-4 flex flex-col gap-3 p-4 sm:flex-row">
            @csrf
            <label class="sr-only" for="note">Note</label>
            <input class="field" id="note" name="note" placeholder="Note de version">
            <button class="btn btn-primary" type="submit">Enregistrer une version</button>
        </form>
    @endif
    <ul class="mt-6 space-y-3">
        @forelse ($versions as $version)
            @if ($historyEnabled || $version->kind === 'safety')
                <li class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium">Version {{ $version->version_number }} · {{ $version->kind === 'safety' ? 'secours' : 'manuelle' }}</p>
                        <p class="text-sm text-slate-600">{{ $version->note }} · {{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('cvs.versions.restore', [$resume, $version]) }}">@csrf<button class="btn btn-secondary" type="submit">Restaurer</button></form>
                </li>
            @endif
        @empty
            <li class="text-sm text-slate-600">Aucune version pour le moment.</li>
        @endforelse
    </ul>
@endsection
