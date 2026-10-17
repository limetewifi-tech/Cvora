@extends('layouts.admin')
@section('document-title', 'IA')
@section('content')
    <h1 class="h2">Quotas et fournisseur IA</h1>
    <p class="small">Pilote : {{ $status['driver'] }}. Repli : {{ $status['fallback'] !== '' ? $status['fallback'] : 'aucun' }}. Aucune clé n'est affichée. Cet écran n'appelle aucun fournisseur.</p>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @foreach ($status['providers'] as $name => $provider)
            <li class="card"><div class="card-body py-3">{{ $name }} · clé {{ $provider['key_present'] ? 'présente' : 'absente' }} · modèle {{ $provider['model'] !== '' ? $provider['model'] : 'absent' }}</div></li>
        @endforeach
    </ul>
    <h2 class="h5 mt-4">Utilisation</h2>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($usage as $row)
            <li class="card"><div class="card-body py-3">{{ $row->feature }} · {{ $row->status }} · {{ $row->total }}</div></li>
        @empty
            <li class="text-secondary">Aucun appel enregistré.</li>
        @endforelse
    </ul>
@endsection
