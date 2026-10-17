<div class="center">
    <h1 style="font-size:22pt;font-weight:normal;">{{ $document['name'] }}</h1>
    @if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
    @if ($document['contact'])<p style="font-size:10pt;">{{ implode(' · ', $document['contact']) }}</p>@endif
</div>
<div class="rule"></div>
@include('resumes.templates._flow')
