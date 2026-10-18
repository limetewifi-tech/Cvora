<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? $document['name'] }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        @php
            $fontFaces = ['sans' => 'DejaVu Sans, sans-serif', 'serif' => 'DejaVu Serif, serif', 'mono' => 'DejaVu Sans Mono, monospace'];
            $fontSizes = ['s' => '9.5pt', 'm' => '10.5pt', 'l' => '12pt'];
            $nameSizes = ['s' => '16pt', 'm' => '18pt', 'l' => '22pt'];
            $fontKey = $document['font'] ?? 'sans';
            $sizeKey = $document['size'] ?? 'm';
        @endphp
        body { margin: 0; color: #1c1917; font-family: {{ $fontFaces[$fontKey] ?? $fontFaces['sans'] }}; font-size: {{ $fontSizes[$sizeKey] ?? '10.5pt' }}; line-height: 1.35; overflow-wrap: break-word; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .mast, .band, .gold, .tag, h2 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: {{ $nameSizes[$sizeKey] ?? '18pt' }}; line-height: 1.15; }
        h2 { font-size: 11pt; margin: 11pt 0 4pt; page-break-after: avoid; }
        .section { margin-bottom: 2pt; }
        .item { margin: 0 0 7pt; page-break-inside: avoid; }
        .primary { font-weight: bold; }
        .meta { color: #4B5563; font-size: 9.5pt; }
        .tag { display: inline-block; border: 1px solid #2563EB; color: #1D4ED8; padding: 1px 6px; margin: 0 4px 4px 0; font-size: 9pt; }
        .mast { padding: 12px 14px; }
        .tpl-moderne .mast { background: {{ $document['primary'] }}; color: #fff; }
        .tpl-moderne h2 { color: {{ $document['primary'] }}; border-left: 4px solid {{ $document['primary'] }}; padding-left: 8px; }
        .tpl-classique h1, .tpl-classique .center { text-align: center; }
        .tpl-classique .rule { border-top: 3px double {{ $document['accent'] }}; margin: 8px 0 12px; }
        .tpl-classique h2 { text-align: center; letter-spacing: 1px; text-transform: uppercase; font-size: 10pt; }
        .tpl-minimaliste { padding: 8px 10px; }
        .tpl-minimaliste h1 { font-weight: normal; letter-spacing: 3px; text-transform: uppercase; }
        .tpl-minimaliste h2 { font-size: 9pt; letter-spacing: 2px; text-transform: uppercase; color: #78716c; font-weight: normal; border-bottom: 0; }
        .tpl-etudiant .accent-bar { height: 8px; background: {{ $document['primary'] }}; }
        .tpl-etudiant h1 { letter-spacing: -0.4px; }
        .tpl-etudiant h2 { background: {{ $document['primary'] }}; color: #fff; padding: 2px 8px; }
        .layout-side, .layout-cols { width: 100%; border-collapse: collapse; }
        .layout-side td, .layout-cols td { vertical-align: top; }
        .tpl-experimente .side { width: 32%; background: {{ $document['primary'] }}; color: #fff; padding: 14px 12px; }
        .tpl-experimente .side h1, .tpl-experimente .side h2, .tpl-experimente .side p { color: #fff; }
        .tpl-experimente .side h2 { border: 0; margin-top: 12px; font-size: 10pt; }
        .tpl-experimente .main { padding: 12px 14px; }
        .tpl-technique .cols td { width: 50%; padding: 0 10px 0 0; }
        .tpl-technique .cols td + td { padding: 0 0 0 10px; border-left: 2px solid {{ $document['primary'] }}; }
        .tpl-administratif h2 { color: #1c1917; border-bottom: 1px solid #1c1917; background: transparent; }
        .tpl-administratif .mast, .tpl-administratif .tag { background: transparent; color: #1c1917; border-color: #1c1917; }
        .tpl-international h1 { text-align: center; font-weight: normal; letter-spacing: 3px; text-transform: uppercase; }
        .tpl-international h2 { border-bottom: 1px solid {{ $document['accent'] }}; text-transform: uppercase; letter-spacing: 2px; font-size: 9pt; font-weight: normal; color: #44403c; }
        .pad { padding: 12px 14px; }
    </style>
    @if ($screen ?? false)
        <style>
            body.is-screen { background: #e5e7eb; }
            body.is-screen .a4-toolbar { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 16px; background: #0f172a; color: #f8fafc; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            body.is-screen .a4-toolbar button { border: 0; border-radius: 6px; background: #2563eb; color: #fff; padding: 8px 12px; cursor: pointer; }
            body.is-screen .a4-stage { display: flex; justify-content: center; padding: 16px 0 32px; }
            body.is-screen .a4-sheet { width: 210mm; min-height: 297mm; padding: 14mm 12mm; background-color: #fff; background-image: linear-gradient(to bottom, transparent calc(297mm - 0.4mm), #cbd5e1 calc(297mm - 0.4mm), #cbd5e1 297mm); background-size: 100% 297mm; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16); }
            body.is-embedded { background: #fff; }
            body.is-embedded .a4-stage { display: block; padding: 0; }
            body.is-embedded .a4-sheet { box-shadow: none; }
            @media print {
                body.is-screen { background: #fff; }
                body.is-screen .a4-toolbar { display: none; }
                body.is-screen .a4-stage { display: block; height: auto !important; padding: 0; }
                body.is-screen .a4-sheet { width: auto; min-height: 0; padding: 0; background: none; box-shadow: none; transform: none !important; }
            }
        </style>
    @endif
</head>
<body class="tpl-{{ $document['slug'] }}@if ($screen ?? false) is-screen @endif @if ($embedded ?? false) is-embedded @endif">
    @if ($screen ?? false)
        @unless ($embedded ?? false)
            <div class="a4-toolbar">
                <p>Format A4 · 210 × 297 mm · marges 14 mm en haut et en bas, 12 mm sur les côtés. Laissez les graphiques d'arrière-plan activés pour conserver les couleurs du modèle.</p>
                <button type="button" data-print-sheet>Imprimer ou enregistrer en PDF</button>
            </div>
        @endunless
        <div class="a4-stage">
            <div class="a4-sheet">
                @includeIf('resumes.templates.'.$document['slug'], ['document' => $document])
            </div>
        </div>
        @if ($embedded ?? false)
            <script>
                (function () {
                    var sheet = document.querySelector('.a4-sheet');
                    var fit = function () {
                        if (!sheet) return;
                        var pageWidth = 210 * 96 / 25.4;
                        sheet.style.width = '210mm';
                        sheet.style.minHeight = '297mm';
                        var scale = Math.max(0.2, document.documentElement.clientWidth / pageWidth);
                        sheet.style.transform = 'scale(' + scale + ')';
                        sheet.style.transformOrigin = 'top left';
                        document.body.style.margin = '0';
                        document.body.style.height = (297 * 96 / 25.4 * scale) + 'px';
                        document.body.style.overflow = 'hidden';
                    };
                    fit();
                    window.addEventListener('resize', fit);
                }());
            </script>
        @else
            <script>
                (function () {
                    var stage = document.querySelector('.a4-stage');
                    var sheet = document.querySelector('.a4-sheet');
                    var button = document.querySelector('[data-print-sheet]');
                    var fit = function () {
                        if (!stage || !sheet) return;
                        var pageWidth = 210 * 96 / 25.4;
                        var scale = Math.min(1, Math.max(0.35, (document.documentElement.clientWidth - 32) / pageWidth));
                        sheet.style.transform = 'scale(' + scale + ')';
                        sheet.style.transformOrigin = 'top center';
                        stage.style.height = (sheet.offsetHeight * scale + 32) + 'px';
                    };
                    if (button) button.addEventListener('click', function () { window.print(); });
                fit();
                window.addEventListener('resize', fit);
            }());
            </script>
        @endif
    @else
        @includeIf('resumes.templates.'.$document['slug'], ['document' => $document])
    @endif
</body>
</html>
