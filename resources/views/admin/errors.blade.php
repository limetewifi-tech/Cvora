@extends('layouts.admin')
@section('document-title', 'Journaux')
@section('content')
    <h1 class="text-3xl font-semibold">Journaux techniques</h1>
    <ul class="mt-6 space-y-2 text-sm">
        @foreach ($logs as $error)
            <li class="card p-4"><p class="font-semibold">{{ $error->exception }}</p><p>{{ $error->message }}</p><p class="text-xs text-slate-500">{{ $error->file }}:{{ $error->line }} · {{ $error->url }} · {{ $error->created_at }}</p></li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
