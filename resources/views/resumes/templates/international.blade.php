<h1 style="font-size:18pt;">{{ mb_strtoupper($document['name']) }}</h1>
<p>{{ $document['headline'] }}</p>
<p class="meta" style="margin-top:4px;">{{ implode(' | ', $document['contact']) }}</p>
<div style="margin-top:10px;">@include('resumes.templates._flow', ['bilingual' => true, 'summaryLabel' => 'Profil / Profile'])</div>
