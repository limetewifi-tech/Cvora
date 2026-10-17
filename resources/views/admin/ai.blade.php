@extends('layouts.admin')
@section('document-title', 'IA')
@section('content')
    <h1 class="text-3xl font-semibold">Quotas et fournisseur IA</h1>
    <p class="mt-2 text-sm">Pilote : {{ $driver }}. Clé API : {{ $configured ? 'configurée' : 'absente' }}. Modèle : {{ $model }}. La clé n'est jamais affichée.</p>
    <ul class="mt-6 space-y-2 text-sm">@foreach ($usage as $row)<li class="card p-3">{{ $row->feature }} · {{ $row->status }} · {{ $row->total }}</li>@endforeach</ul>
@endsection
