@extends('layouts.admin')
@section('document-title', 'Abonnements')
@section('content')
    <h1 class="h2">Abonnements</h1>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @foreach ($subscriptions as $subscription)
            <li class="card">
                <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <span>{{ $subscription->user?->email }} · {{ $subscription->plan?->name }} · {{ $subscription->status->label() }} · {{ $subscription->credits_remaining }} crédits</span>
                    @if ($subscription->status->value === 'active')
                        <x-confirm-form
                            :id="'cancel-subscription-'.$subscription->id"
                            title="Annuler cet abonnement"
                            :action="route('admin.subscriptions.cancel', $subscription)"
                            trigger="Annuler"
                            submit="Annuler l'abonnement"
                            trigger-class="btn btn-outline-primary"
                            method="POST"
                        >
                            <p class="mb-0">L'abonnement actif de ce compte sera annulé. Le contenu des documents n'est pas affiché.</p>
                        </x-confirm-form>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    <div class="mt-3">{{ $subscriptions->links() }}</div>
@endsection
