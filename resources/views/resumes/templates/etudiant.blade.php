<div class="accent-bar"></div>
<div class="pad">
    <h1>{{ $document['name'] }}</h1>
    @if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
    @if ($document['contact'])<p class="meta" style="margin-top:6px;">{{ implode(' · ', $document['contact']) }}</p>@endif
    @include('resumes.templates._flow')
</div>
