@extends('layouts.app')
@section('document-title', 'Bac à sable')
@section('content')
    <h1 class="text-3xl font-semibold">Paiement bac à sable</h1>
    <p class="mt-3 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Aucun argent n'est débité. Cette page simule uniquement le retour d'un prestataire pour tester l'activation d'une offre.</p>
    <div class="card mt-6 max-w-lg space-y-2 p-5 text-sm">
        <p>Référence : {{ $transaction->reference }}</p>
        <p>Montant : {{ $transaction->amount }} {{ $transaction->currency }}</p>
        <p>Offre : {{ $transaction->plan?->name }}</p>
        <p>Statut : {{ $transaction->status->label() }}</p>
    </div>
    @if ($transaction->status->value === 'pending')
        <div class="mt-4 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('billing.sandbox.confirm', $transaction) }}">@csrf<button class="btn btn-primary" type="submit">Simuler une confirmation</button></form>
            <form method="POST" action="{{ route('billing.sandbox.fail', $transaction) }}">@csrf<button class="btn btn-secondary" type="submit">Simuler un échec</button></form>
        </div>
    @endif
@endsection
