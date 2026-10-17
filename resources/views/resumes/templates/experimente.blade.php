<div class="mast">
    <h1 style="font-size:24pt;">{{ $document['name'] }}</h1>
    <p style="font-size:12pt;margin-top:4px;">{{ $document['headline'] }}</p>
    <p style="font-size:10pt;margin-top:8px;color:#E5E7EB;">{{ implode(' · ', $document['contact']) }}</p>
</div>
<div class="gold"></div>
<div class="pad">@include('resumes.templates._flow', ['summaryLabel' => 'Synthèse'])</div>
