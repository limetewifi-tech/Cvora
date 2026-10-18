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
        table, td, h2, .tag, .side, .band, .exec-head, .color-bar, .gold-line, .monogram { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: {{ $nameSizes[$sizeKey] ?? '18pt' }}; line-height: 1.15; }
        h2 { font-size: 11pt; margin: 10pt 0 4pt; page-break-after: avoid; }
        .section { margin-bottom: 2pt; }
        .item, .job { margin: 0 0 7pt; page-break-inside: avoid; }
        .primary { font-weight: bold; }
        .meta, .level { color: #57534e; font-size: 9pt; }
        .role { margin-top: 1mm; }
        .identity, .split, .band, .exec-head, .skill-row { width: 100%; border-collapse: collapse; }
        .identity td, .split td, .band td, .exec-head td { vertical-align: top; }
        .photo-cell { width: 32mm; }
        .photo, .monogram { display: block; }
        .photo-portrait, .monogram-portrait { width: 32mm; height: 42mm; }
        .photo-square, .monogram-square { width: 26mm; height: 26mm; }
        .photo-circle, .monogram-circle { width: 28mm; height: 28mm; }
        .monogram { text-align: center; font-weight: bold; background: #fff; color: #1c1917; }
        .monogram-portrait { line-height: 42mm; font-size: 16pt; }
        .monogram-square { line-height: 26mm; font-size: 13pt; }
        .monogram-circle { line-height: 28mm; font-size: 13pt; }
        .contact-line { margin: 0 0 2mm; }
        .contact-label { font-size: 8pt; letter-spacing: 0.4px; text-transform: uppercase; }
        .tag { display: inline-block; border: 1px solid {{ $document['primary'] }}; color: {{ $document['primary'] }}; padding: 0.4mm 1.4mm; margin: 0 1mm 1mm 0; font-size: 8.5pt; }
        .split .narrow { width: 36%; }
        .split td + td { padding-left: 4mm; }
        .h-rule, .h-exec { color: {{ $document['primary'] }}; border-bottom: 0.3mm solid {{ $document['primary'] }}; padding-bottom: 0.6mm; font-size: 10pt; }
        .h-center { text-align: center; letter-spacing: 1.4px; text-transform: uppercase; font-size: 9pt; font-weight: normal; }
        .h-hair { font-weight: normal; letter-spacing: 2px; text-transform: uppercase; font-size: 8.5pt; color: #57534e; border-bottom: 0.2mm solid #e7e5e4; }
        .h-bar { background: {{ $document['primary'] }}; color: #fff; padding: 0.8mm 2mm; font-size: 8.5pt; letter-spacing: 0.6px; text-transform: uppercase; }
        .h-side { color: #fff; font-size: 8.5pt; letter-spacing: 1px; text-transform: uppercase; border-bottom: 0.2mm solid #d6d3d1; margin-top: 3.5mm; }
        .h-tech { color: {{ $document['primary'] }}; font-size: 9pt; letter-spacing: 0.6px; text-transform: uppercase; }
        .layout-corporate .side, .layout-blue .side { width: 34%; padding: 3.5mm; }
        .layout-corporate .side h1, .layout-corporate .side p, .layout-blue .side h1, .layout-blue .side p { color: #fff; }
        .layout-corporate .contact-label, .layout-blue .contact-label { color: #e7e5e4; }
        .layout-corporate .monogram, .layout-blue .monogram { border: 0.4mm solid #fff; margin-bottom: 3mm; }
        .layout-corporate .photo, .layout-blue .photo { margin-bottom: 3mm; }
        .layout-blue .tag { background: #fff; color: #1c1917; border-color: #fff; }
        .layout-blue .job { border-left: 0.7mm solid {{ $document['primary'] }}; padding-left: 2.5mm; }
        .layout-classic h1 { font-weight: normal; }
        .layout-classic .photo-cell { text-align: right; }
        .double-rule { border-top: 0.8mm double {{ $document['accent'] }}; margin: 3mm 0 2mm; }
        .layout-elegant .band { background: #f4efe8; margin-bottom: 4mm; }
        .layout-elegant .band td { padding: 3.5mm; }
        .layout-elegant h1 { font-weight: normal; letter-spacing: 0.6px; }
        .layout-modern .color-bar { height: 2.2mm; background: {{ $document['primary'] }}; margin: 3mm 0 1mm; }
        .layout-modern .item { border-left: 0.6mm solid {{ $document['primary'] }}; padding-left: 2.5mm; }
        .layout-minimal h1 { font-weight: normal; letter-spacing: 2.5px; text-transform: uppercase; }
        .layout-minimal .photo-circle, .layout-minimal .monogram-circle { width: 22mm; height: 22mm; }
        .layout-minimal .monogram-circle { line-height: 22mm; }
        .layout-minimal .photo-cell { text-align: right; }
        .hairline { border-top: 0.2mm solid #d6d3d1; margin: 4mm 0 2mm; }
        .layout-executive .exec-head td { padding: 4mm 3mm; color: #fff; }
        .layout-executive .exec-head h1, .layout-executive .exec-head p { color: #fff; }
        .layout-executive .kicker { letter-spacing: 1.8px; text-transform: uppercase; font-size: 8pt; }
        .layout-executive .gold-line { height: 1.1mm; background: {{ $document['accent'] }}; margin: 0 0 4mm; }
        .layout-executive .monogram { border: 0.4mm solid #fff; }
        .layout-technical .tech-head { border-bottom: 0.6mm solid {{ $document['primary'] }}; margin-bottom: 3mm; }
        .layout-technical .tech-head td { padding-bottom: 3mm; }
        .layout-technical .narrow { background: #f5f5f4; padding: 3mm; }
        .layout-technical .photo-square, .layout-technical .monogram-square { width: 18mm; height: 18mm; }
        .layout-technical .monogram-square { line-height: 18mm; font-size: 10pt; }
        .skill-row td { padding: 0.4mm 0; }
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
<body class="tpl-{{ $document['slug'] }} layout-{{ $document['layout'] }}@if ($screen ?? false) is-screen @endif @if ($embedded ?? false) is-embedded @endif">
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
