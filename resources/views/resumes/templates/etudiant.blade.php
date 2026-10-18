<table class="split">
    <tr>
        <td class="side" style="background: {{ $document['primary'] }};">
            @include('resumes.templates._photo')
            <h1>{{ $document['name'] }}</h1>
            @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
            @include('resumes.templates._contacts')
            @include('resumes.templates._blocks', ['keys' => ['skills', 'languages', 'interests'], 'variant' => 'side', 'asChips' => true])
        </td>
        <td class="main">
            @if ($document['summary'] !== '')
                <div class="section">
                    <h2 class="h-rule">Profil</h2>
                    <p>{!! nl2br(e($document['summary'])) !!}</p>
                </div>
            @endif
            @php $jobs = $document['by']['experiences'] ?? null; @endphp
            @if ($jobs)
                <div class="section">
                    <h2 class="h-rule">{{ $jobs['label'] }}</h2>
                    @foreach ($jobs['items'] as $item)
                        <div class="job">
                            <p class="primary">{{ $item['primary'] }}</p>
                            @if ($item['secondary'] !== '' || $item['dates'] !== '')
                                <p class="meta">{{ trim($item['secondary'].($item['dates'] !== '' ? ' · '.$item['dates'] : '')) }}</p>
                            @endif
                            @if ($item['body'] !== '')<p>{!! nl2br(e($item['body'])) !!}</p>@endif
                        </div>
                    @endforeach
                </div>
            @endif
            @include('resumes.templates._blocks', ['keys' => ['education', 'projects', 'certifications', 'references'], 'variant' => 'rule'])
        </td>
    </tr>
</table>
