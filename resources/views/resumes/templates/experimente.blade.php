@php
    $sideKeys = ['skills', 'languages'];
@endphp
<table class="layout-side">
    <tr>
        <td class="side">
            <h1>{{ $document['name'] }}</h1>
            @if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
            @foreach ($document['contact'] as $line)
                <p>{{ $line }}</p>
            @endforeach
            @foreach ($document['sections'] as $section)
                @continue(! in_array($section['key'], $sideKeys, true))
                <h2>{{ $section['label'] }}</h2>
                @foreach ($section['items'] as $item)
                    <p>{{ $item['primary'] }}@if ($item['secondary']) · {{ $item['secondary'] }}@endif</p>
                @endforeach
            @endforeach
        </td>
        <td class="main">
            @include('resumes.templates._flow', ['skip' => $sideKeys, 'summaryLabel' => 'Parcours'])
        </td>
    </tr>
</table>
