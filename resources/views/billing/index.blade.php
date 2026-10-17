@extends('layouts.app')
@section('document-title', 'Abonnement')
@section('content')
    <h1 class="h2">Abonnement et facturation</h1>
    <p class="text-secondary">Offre actuelle : {{ $subscription?->plan?->name ?? 'Aucune' }} · {{ $subscription?->credits_remaining ?? 0 }} crédits IA.</p>
    @if ($sandbox)
        <div class="alert alert-warning">Mode bac à sable. Aucun paiement réel n'est encaissé. Le prestataire de production est UniPay, inactif tant que ses identifiants ne sont pas configurés.</div>
    @endif
    <div class="row g-3 mt-1">
        @foreach ($plans as $plan)
            <div class="col-lg-4">
                <article class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h4">{{ $plan->name }}</h2>
                        <p class="h3 fw-semibold">{{ $plan->isFree() ? 'Gratuit' : number_format((float) $plan->price, 0, ',', ' ').' '.$plan->currency }}</p>
                        <p class="text-secondary">{{ $plan->description }}</p>
                        <form method="POST" action="{{ route('billing.checkout', $plan) }}" class="mt-auto">
                            @csrf
                            <button class="btn btn-primary" type="submit" data-loading>{{ $subscription?->plan_id === $plan->id ? 'Offre actuelle' : ($plan->isFree() ? 'Revenir à cette offre' : 'Payer cette offre') }}</button>
                        </form>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <h2 class="h5 mt-4">Transactions</h2>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($transactions as $transaction)
            <li class="card"><div class="card-body py-3">{{ $transaction->reference }} · {{ $transaction->amount }} {{ $transaction->currency }} · {{ $transaction->status->label() }} · {{ $transaction->provider }}</div></li>
        @empty
            <li>@include('components.empty', ['icon' => 'bi-receipt', 'title' => 'Aucune transaction.'])</li>
        @endforelse
    </ul>
@endsection
