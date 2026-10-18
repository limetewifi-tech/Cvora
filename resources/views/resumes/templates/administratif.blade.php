<h1>{{ $document['name'] }}</h1>
@if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
@if ($document['contact'])<p class="meta">{{ implode(' · ', $document['contact']) }}</p>@endif
@include('resumes.templates._flow', ['summaryLabel' => 'Profil'])
