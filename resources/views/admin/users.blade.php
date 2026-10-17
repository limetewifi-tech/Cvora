@extends('layouts.admin')
@section('document-title', 'Comptes')
@section('content')
    <h1 class="text-3xl font-semibold">Comptes</h1>
    <div class="mt-6 space-y-3">
        @foreach ($users as $user)
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card grid gap-3 p-4 md:grid-cols-5">
                @csrf @method('PUT')
                <div><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->email }}</p><p class="text-xs">{{ $user->subscription?->plan?->name }}</p></div>
                <select class="field" name="role"><option value="user" @selected($user->role->value === 'user')>Utilisateur</option><option value="admin" @selected($user->role->value === 'admin')>Administrateur</option></select>
                <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked($user->is_active)> Actif</label>
                <label class="text-sm"><input type="checkbox" name="anonymize" value="1"> Anonymiser</label>
                <button class="btn btn-secondary" type="submit">Enregistrer</button>
            </form>
        @endforeach
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
