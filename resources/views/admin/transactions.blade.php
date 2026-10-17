@extends('layouts.admin')
@section('document-title', 'Transactions')
@section('content')
    <h1 class="text-3xl font-semibold">Transactions</h1>
    <ul class="mt-6 space-y-2 text-sm">
        @foreach ($transactions as $transaction)
            <li class="card p-4">{{ $transaction->reference }} · {{ $transaction->user?->email }} · {{ $transaction->plan?->name }} · {{ $transaction->amount }} {{ $transaction->currency }} · {{ $transaction->status->label() }} · {{ $transaction->provider }}</li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
