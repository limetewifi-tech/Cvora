@extends('layouts.app')
@section('document-title', 'Abonnement')
@section('content')
    <h1 class="text-3xl font-semibold">Abonnement et facturation</h1>
    <p class="mt-2 text-slate-600">Offre actuelle : {{ $subscription?->plan?->name ?? 'Aucune' }} · {{ $subscription?->credits_remaining ?? 0 }} crédits IA.</p>
    @if ($sandbox)
        <p class="mt-4 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Mode bac à sable. Aucun paiement réel n'est encaissé. Le prestataire de production est UniPay, inactif tant que ses identifiants ne sont pas configurés.</p>
    @endif
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        @foreach ($plans as $plan)
            <article class="card p-5">
                <h2 class="text-xl font-semibold">{{ $plan->name }}</h2>
                <p class="mt-2 text-2xl font-semibold">{{ $plan->isFree() ? 'Gratuit' : number_format((float) $plan->price, 0, ',', ' ').' '.$plan->currency }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $plan->description }}</p>
                <form method="POST" action="{{ route('billing.checkout', $plan) }}" class="mt-4">@csrf<button class="btn btn-primary" type="submit">{{ $subscription?->plan_id === $plan->id ? 'Offre actuelle' : ($plan->isFree() ? 'Revenir à cette offre' : 'Payer cette offre') }}</button></form>
            </article>
        @endforeach
    </div>
    <h2 class="mt-8 text-lg font-semibold">Transactions</h2>
    <ul class="mt-3 space-y-2 text-sm">
        @forelse ($transactions as $transaction)
            <li class="card px-4 py-3">{{ $transaction->reference }} · {{ $transaction->amount }} {{ $transaction->currency }} · {{ $transaction->status->label() }} · {{ $transaction->provider }}</li>
        @empty
            <li class="text-slate-600">Aucune transaction.</li>
        @endforelse
    </ul>
@endsection
