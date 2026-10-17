@extends('layouts.app')
@section('document-title', 'Bac à sable')
@section('content')
    <h1 class="h2">Paiement bac à sable</h1>
    <div class="alert alert-warning">Aucun argent n'est débité. Cette page simule uniquement le retour d'un prestataire pour tester l'activation d'une offre.</div>
    <div class="card cvora-narrow">
        <div class="card-body">
            <p class="mb-2">Référence : {{ $transaction->reference }}</p>
            <p class="mb-2">Montant : {{ $transaction->amount }} {{ $transaction->currency }}</p>
            <p class="mb-2">Offre : {{ $transaction->plan?->name }}</p>
            <p class="mb-0">Statut : {{ $transaction->status->label() }}</p>
        </div>
    </div>
    @if ($transaction->status->value === 'pending')
        <div class="d-flex flex-wrap gap-2 mt-3">
            <form method="POST" action="{{ route('billing.sandbox.confirm', $transaction) }}">@csrf<button class="btn btn-primary" type="submit" data-loading>Simuler une confirmation</button></form>
            <form method="POST" action="{{ route('billing.sandbox.fail', $transaction) }}">@csrf<button class="btn btn-outline-primary" type="submit" data-loading>Simuler un échec</button></form>
        </div>
    @endif
@endsection
