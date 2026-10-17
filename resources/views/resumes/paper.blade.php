<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? $document['name'] }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #1F2937; font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.4; }
        h1, h2, h3, p { margin: 0; }
        h2 { font-size: 12pt; margin: 12px 0 6px; page-break-after: avoid; }
        .section { margin-bottom: 8px; }
        .item { margin: 0 0 8px; }
        .primary { font-weight: bold; }
        .meta { color: #4B5563; font-size: 10pt; }
        .tag { display: inline-block; border: 1px solid #2563EB; color: #1D4ED8; padding: 1px 6px; margin: 0 4px 4px 0; font-size: 9pt; }
        .mast { padding: 16px 18px; }
        .tpl-moderne .mast { background: {{ $document['primary'] }}; color: #fff; }
        .tpl-moderne h2 { color: {{ $document['primary'] }}; border-left: 4px solid {{ $document['primary'] }}; padding-left: 8px; }
        .tpl-classique { font-family: DejaVu Serif, serif; }
        .tpl-classique h1, .tpl-classique .center { text-align: center; }
        .tpl-classique .rule { border-top: 3px double #1F2937; margin: 8px 0 12px; }
        .tpl-classique h2 { text-align: center; font-variant: small-caps; letter-spacing: 1px; }
        .tpl-minimaliste { padding: 8px 18px; }
        .tpl-minimaliste h1 { font-weight: normal; letter-spacing: 2px; }
        .tpl-minimaliste h2 { font-size: 9pt; letter-spacing: 2px; text-transform: uppercase; color: #6B7280; font-weight: normal; }
        .tpl-etudiant .band { background: #DBEAFE; border-top: 8px solid {{ $document['primary'] }}; padding: 14px 16px; }
        .tpl-etudiant .edu { background: #F8FAFC; border: 1px solid #DBEAFE; padding: 8px; }
        .tpl-experimente .mast { background: #1F2937; color: #fff; padding: 22px 18px; }
        .tpl-experimente .gold { height: 4px; background: #F59E0B; }
        .tpl-technique .mast { background: #0F172A; color: #E2E8F0; padding: 14px 16px; font-family: DejaVu Sans Mono, monospace; }
        .tpl-administratif table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .tpl-administratif td { border: 1px solid #D1D5DB; padding: 4px 6px; font-size: 10pt; }
        .tpl-administratif .label { width: 28%; background: #F3F4F6; font-weight: bold; }
        .tpl-international h2 { border-bottom: 1px solid #111827; text-transform: uppercase; font-size: 10pt; }
        .pad { padding: 16px 18px; }
    </style>
</head>
<body class="tpl-{{ $document['slug'] }}">
    @includeIf('resumes.templates.'.$document['slug'], ['document' => $document])
</body>
</html>
