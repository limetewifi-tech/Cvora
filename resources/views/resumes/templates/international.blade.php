<h1>{{ $document['name'] }}</h1>
@if ($document['headline'])<p class="center">{{ $document['headline'] }}</p>@endif
@if ($document['contact'])<p class="center meta">{{ implode(' · ', $document['contact']) }}</p>@endif
<div class="rule" style="border-top:1px solid {{ $document['accent'] }};margin:10px 0;"></div>
@include('resumes.templates._flow', ['summaryLabel' => 'Profil'])
