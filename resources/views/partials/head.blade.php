<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
    (function () {
        try {
            var stored = localStorage.getItem('cvora-theme');
            var theme = stored === 'dark' || stored === 'light'
                ? stored
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        } catch (e) {}
    })();
</script>
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700&display=swap" rel="stylesheet">
@include('partials.assets')
