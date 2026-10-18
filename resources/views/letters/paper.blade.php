<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Lettre de motivation' }}</title>
    <style>
        @page { size: A4 portrait; margin: 16mm 16mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #1c1917; font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.45; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1, h2, p { margin: 0; }
        .sheet-table { width: 100%; border-collapse: collapse; }
        .sheet-table td { vertical-align: top; }
        .muted { color: #44403c; font-size: 10pt; }
        .object { margin: 14px 0 12px; font-weight: bold; }
        .body { margin-top: 8px; }
        .sign { margin-top: 22px; text-align: right; }
        .rule { border-top: 1px solid #1c1917; margin: 10px 0 14px; }
        .layout-moderne .band, .layout-bandeau .band { background: #0f766e; color: #fff; padding: 14px 16px; }
        .layout-bandeau .band-object { background: #134e4a; color: #fff; padding: 8px 16px; font-weight: bold; }
        .layout-elegant h1 { font-family: DejaVu Serif, serif; font-weight: normal; letter-spacing: 2px; text-transform: uppercase; font-size: 18pt; text-align: center; }
        .layout-elegant .rule { border-top: 1px solid #0f766e; }
        .pad { padding: 16px 4px 0; }
        .half { width: 50%; }
    </style>
    @if ($screen ?? false)
        <style>
            body.is-screen { background: #e7e5e4; }
            body.is-screen .a4-stage { display: flex; justify-content: center; padding: 16px 0 32px; }
            body.is-screen .a4-sheet { width: 210mm; min-height: 297mm; padding: 16mm; background: #fff; box-shadow: 0 12px 32px rgba(28, 25, 23, 0.16); }
            body.is-embedded { background: #fff; }
            body.is-embedded .a4-stage { display: block; padding: 0; }
            body.is-embedded .a4-sheet { box-shadow: none; }
            @media print {
                body.is-screen { background: #fff; }
                body.is-screen .a4-stage { display: block; padding: 0; }
                body.is-screen .a4-sheet { width: auto; min-height: 0; padding: 0; box-shadow: none; transform: none !important; }
            }
        </style>
    @endif
</head>
<body class="layout-{{ $sheet['layout'] }}@if ($screen ?? false) is-screen @endif @if ($embedded ?? false) is-embedded @endif">
    @if ($screen ?? false)
        <div class="a4-stage"><div class="a4-sheet">
            @include('letters.layouts.'.$sheet['layout'])
        </div></div>
        @if ($embedded ?? false)
            <script>
                (function () {
                    var sheet = document.querySelector('.a4-sheet');
                    var fit = function () {
                        if (!sheet) return;
                        var pageWidth = 210 * 96 / 25.4;
                        sheet.style.width = '210mm';
                        sheet.style.minHeight = '297mm';
                        var width = document.documentElement.clientWidth;
                        if (width < 40) return;
                        var scale = width / pageWidth;
                        sheet.style.transform = 'scale(' + scale + ')';
                        sheet.style.transformOrigin = 'top left';
                        document.body.style.margin = '0';
                        document.body.style.height = (297 * 96 / 25.4 * scale) + 'px';
                        document.body.style.overflow = 'hidden';
                    };
                    fit();
                    window.addEventListener('load', fit);
                    window.addEventListener('resize', fit);
                }());
            </script>
        @endif
    @else
        @include('letters.layouts.'.$sheet['layout'])
    @endif
</body>
</html>
