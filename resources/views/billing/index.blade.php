@extends('layouts.app')
@section('document-title', 'Abonnement')
@section('content')
    <h1 class="h2">Abonnement et facturation</h1>
    <p class="text-secondary">Offre actuelle : {{ $subscription?->plan?->name ?? 'Aucune' }} · {{ $subscription?->credits_remaining ?? 0 }} crédits IA.</p>
    @if ($sandbox)
        <div class="alert alert-warning">Mode bac à sable. Aucun paiement réel n'est encaissé. Le prestataire de production est iKeePay. Il reste inactif tant que IKEEPAY_API_KEY et IKEEPAY_API_URL ne sont pas renseignés.</div>
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
                            @if ($ikeepay && ! $plan->isFree())
                                <div class="vstack gap-2 mb-3">
                                    <label class="form-label" for="phone-{{ $plan->id }}">Numéro Mobile Money</label>
                                    <input class="form-control" id="phone-{{ $plan->id }}" name="phone_number" inputmode="numeric" autocomplete="tel" required pattern="[0-9]{8,15}" value="{{ old('phone_number') }}">
                                    <label class="form-label" for="country-{{ $plan->id }}">Pays</label>
                                    <input class="form-control" id="country-{{ $plan->id }}" name="country" maxlength="2" required pattern="[A-Za-z]{2}" value="{{ old('country', 'CD') }}">
                                    <label class="form-label" for="operator-{{ $plan->id }}">Opérateur</label>
                                    <select class="form-select" id="operator-{{ $plan->id }}" name="operator" required>
                                        @foreach (['ORANGE', 'MTN', 'WAVE', 'MOOV', 'MOBICASH', 'AIRTEL', 'VODACOM'] as $operator)
                                            <option value="{{ $operator }}" @selected(old('operator', 'AIRTEL') === $operator)>{{ $operator }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
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
