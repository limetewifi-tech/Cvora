<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $letter->title }}</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1F2937; font-size: 12pt; line-height: 1.5; }
        h1 { font-size: 16pt; color: #2563EB; margin: 0 0 8px; }
        .meta { color: #4B5563; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>{{ $letter->title }}</h1>
    <p class="meta">{{ trim(implode(' — ', array_filter([$letter->position, $letter->company]))) }}</p>
    <div>{!! nl2br(e($letter->body)) !!}</div>
</body>
</html>
