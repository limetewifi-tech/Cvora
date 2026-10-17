@if (session('success') || session('status') || session('error'))
    <div class="mb-4 space-y-2" role="status">
        @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'status' => 'border-blue-200 bg-blue-50 text-blue-800', 'error' => 'border-amber-200 bg-amber-50 text-amber-900'] as $key => $classes)
            @if (session($key))
                <p class="rounded-2xl border px-4 py-3 text-sm {{ $classes }}">{{ session($key) }}</p>
            @endif
        @endforeach
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <p class="font-semibold">Certaines informations doivent être corrigées.</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
