@extends('layouts.app')
@section('document-title', 'Versions')
@section('content')
    <h1 class="h2">Versions de « {{ $resume->title }} »</h1>
    @unless ($historyEnabled)
        <div class="alert alert-primary mt-3">Les versions de secours restent disponibles. L'historique manuel complet est inclus dans l'offre Pro.</div>
    @endunless
    @if ($historyEnabled)
        <form method="POST" action="{{ route('cvs.versions.store', $resume) }}" class="card mt-3">
            @csrf
            <div class="card-body d-flex flex-column flex-sm-row gap-3">
                <label class="visually-hidden" for="note">Note</label>
                <input class="form-control" id="note" name="note" placeholder="Note de version">
                <button class="btn btn-primary" type="submit" data-loading>Enregistrer une version</button>
            </div>
        </form>
    @endif
    <ul class="list-unstyled vstack gap-3 mt-4">
        @forelse ($versions as $version)
            @if ($historyEnabled || $version->kind === 'safety')
                <li class="card">
                    <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                        <div>
                            <p class="fw-semibold mb-1">Version {{ $version->version_number }} · {{ $version->kind === 'safety' ? 'secours' : 'manuelle' }}</p>
                            <p class="small text-secondary mb-0">{{ $version->note }} · {{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                        </div>
                        <form method="POST" action="{{ route('cvs.versions.restore', [$resume, $version]) }}">@csrf<button class="btn btn-outline-primary" type="submit">Restaurer</button></form>
                    </div>
                </li>
            @endif
        @empty
            <li class="text-secondary">Aucune version pour le moment.</li>
        @endforelse
    </ul>
@endsection
