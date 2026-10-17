<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? $document['name'] }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; line-height: 1.35; overflow-wrap: break-word; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .mast, .band, .gold, .tag, h2 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 18pt; line-height: 1.15; }
        h2 { font-size: 11pt; margin: 11pt 0 4pt; page-break-after: avoid; }
        .section { margin-bottom: 2pt; }
        .item { margin: 0 0 7pt; page-break-inside: avoid; }
        .primary { font-weight: bold; }
        .meta { color: #4B5563; font-size: 9.5pt; }
        .tag { display: inline-block; border: 1px solid #2563EB; color: #1D4ED8; padding: 1px 6px; margin: 0 4px 4px 0; font-size: 9pt; }
        .mast { padding: 12px 14px; }
        .tpl-moderne .mast { background: {{ $document['primary'] }}; color: #fff; }
        .tpl-moderne h2 { color: {{ $document['primary'] }}; border-left: 4px solid {{ $document['primary'] }}; padding-left: 8px; }
        .tpl-classique { font-family: DejaVu Serif, serif; }
        .tpl-classique h1, .tpl-classique .center { text-align: center; }
        .tpl-classique .rule { border-top: 3px double #1F2937; margin: 8px 0 12px; }
        .tpl-classique h2 { text-align: center; font-variant: small-caps; letter-spacing: 1px; }
        .tpl-minimaliste { padding: 4px 8px; }
        .tpl-minimaliste h1 { font-weight: normal; letter-spacing: 2px; }
        .tpl-minimaliste h2 { font-size: 9pt; letter-spacing: 2px; text-transform: uppercase; color: #6B7280; font-weight: normal; }
        .tpl-etudiant .band { background: #DBEAFE; border-top: 8px solid {{ $document['primary'] }}; padding: 12px 14px; }
        .tpl-etudiant .edu { background: #F8FAFC; border: 1px solid #DBEAFE; padding: 8px; }
        .tpl-experimente .mast { background: #1F2937; color: #fff; padding: 16px 14px; }
        .tpl-experimente .gold { height: 4px; background: #F59E0B; }
        .tpl-technique .mast { background: #0F172A; color: #E2E8F0; padding: 12px 14px; font-family: DejaVu Sans Mono, monospace; }
        .tpl-administratif table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .tpl-administratif td { border: 1px solid #D1D5DB; padding: 4px 6px; font-size: 10pt; }
        .tpl-administratif .label { width: 28%; background: #F3F4F6; font-weight: bold; }
        .tpl-international h2 { border-bottom: 1px solid #111827; text-transform: uppercase; font-size: 10pt; }
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
