@if (session('success') || session('status') || session('error'))
    <div class="mb-3">
        @foreach (['success' => 'success', 'status' => 'primary', 'error' => 'warning'] as $key => $tone)
            @if (session($key))
                <div class="alert alert-{{ $tone }} alert-dismissible fade show" role="status" data-autohide>
                    {{ session($key) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
        @endforeach
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="fw-semibold mb-1">Certaines informations doivent être corrigées.</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
