@extends('layouts.admin')
@section('document-title', 'Comptes')
@section('content')
    <h1 class="h2">Comptes</h1>
    <div class="vstack gap-3 mt-3">
        @foreach ($users as $user)
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <p class="fw-semibold mb-1">{{ $user->name }}</p>
                            <p class="small text-secondary mb-1">{{ $user->email }}</p>
                            <p class="small mb-0">{{ $user->subscription?->plan?->name }}</p>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="role-{{ $user->id }}">Rôle</label>
                            <select class="form-select" id="role-{{ $user->id }}" name="role">
                                <option value="user" @selected($user->role->value === 'user')>Utilisateur</option>
                                <option value="admin" @selected($user->role->value === 'admin')>Administrateur</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="active-{{ $user->id }}" value="1" @checked($user->is_active)>
                                <label class="form-check-label" for="active-{{ $user->id }}">Actif</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" name="anonymize" id="anonymize-{{ $user->id }}" value="1">
                                <label class="form-check-label" for="anonymize-{{ $user->id }}">Anonymiser</label>
                            </div>
                        </div>
                        <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Enregistrer</button></div>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
