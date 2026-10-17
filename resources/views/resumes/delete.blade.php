@extends('layouts.app')
@section('document-title', 'Supprimer le CV')
@section('content')
    <h1 class="h2">Supprimer « {{ $resume->title }} »</h1>
    <p class="text-secondary">Cette action retire le CV et son historique. Elle est définitive.</p>
    <form method="POST" action="{{ route('cvs.destroy', $resume) }}" class="card cvora-narrow mt-4 needs-validation" novalidate>
        @csrf
        @method('DELETE')
        <div class="card-body vstack gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="confirm" id="confirm" value="1" required>
                <label class="form-check-label" for="confirm">Je confirme la suppression.</label>
                <div class="invalid-feedback">Cochez la confirmation pour supprimer le CV.</div>
            </div>
            <button class="btn btn-danger" type="submit" data-loading>Supprimer le CV</button>
        </div>
    </form>
@endsection
