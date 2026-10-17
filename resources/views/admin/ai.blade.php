@extends('layouts.admin')
@section('document-title', 'IA')
@section('content')
    <h1 class="h2">Quotas et fournisseur IA</h1>
    <p class="small">Pilote : {{ $driver }}. Clé API : {{ $configured ? 'configurée' : 'absente' }}. Modèle : {{ $model }}. La clé n'est jamais affichée.</p>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($usage as $row)
            <li class="card"><div class="card-body py-3">{{ $row->feature }} · {{ $row->status }} · {{ $row->total }}</div></li>
        @empty
            <li class="text-secondary">Aucun appel enregistré.</li>
        @endforelse
    </ul>
@endsection
