@extends('layouts.app')
@section('document-title', 'Supprimer le CV')
@section('content')
    <h1 class="text-3xl font-semibold">Supprimer « {{ $resume->title }} »</h1>
    <p class="mt-2 text-slate-600">Cette action retire le CV et son historique. Elle est définitive.</p>
    <form method="POST" action="{{ route('cvs.destroy', $resume) }}" class="card mt-6 max-w-lg space-y-4 p-6">
        @csrf
        @method('DELETE')
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirm" value="1" required> Je confirme la suppression.</label>
        <button class="btn btn-primary" type="submit">Supprimer le CV</button>
    </form>
@endsection
