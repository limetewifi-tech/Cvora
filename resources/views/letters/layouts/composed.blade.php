@php
    $family = $sheet['family'] ?? 'classique';
    $accent = $sheet['accent'] ?? '#0F766E';
@endphp
@if ($family === 'colonne')
    <table class="sheet-table">
        <tr>
            <td class="half" style="background: {{ $accent }}; color: #fff; padding: 12px;">
                @foreach ($sheet['sender'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
            </td>
            <td class="half" style="padding-left: 14px;">
                @foreach ($sheet['recipient'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
                <p class="object">{{ $sheet['object'] }}</p>
                <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
                <p class="sign">{{ $sheet['name'] }}</p>
            </td>
        </tr>
    </table>
@elseif ($family === 'centre')
    <h1 style="text-align: center; font-size: 18pt;">{{ $sheet['name'] }}</h1>
    <p class="object" style="text-align: center;">{{ $sheet['object'] }}</p>
    <p class="muted" style="text-align: center;">{{ implode(' · ', $sheet['sender']) }}</p>
    <div class="rule" style="border-top-color: {{ $accent }};"></div>
    @foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach
    <p class="muted" style="text-align: right;">{{ $sheet['place'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@elseif ($family === 'filet')
    <h1 style="font-size: 18pt;">{{ $sheet['name'] }}</h1>
    <div class="rule" style="border-top: 3px solid {{ $accent }};"></div>
    @foreach ($sheet['sender'] as $line)<p class="muted">{{ $line }}</p>@endforeach
    <p style="margin-top: 12px;">@foreach ($sheet['recipient'] as $line){{ $line }}<br>@endforeach</p>
    <p class="muted">{{ $sheet['place'] }}</p>
    <p class="object">{{ $sheet['object'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@elseif ($family === 'encadre')
    <div style="border: 1px solid {{ $accent }}; padding: 10px 12px; margin-bottom: 14px;">
        @foreach ($sheet['sender'] as $line)<p>{{ $line }}</p>@endforeach
    </div>
    @foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach
    <p class="muted" style="text-align: right;">{{ $sheet['place'] }}</p>
    <p class="object">{{ $sheet['object'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@elseif ($family === 'compact')
    <table class="sheet-table">
        <tr>
            <td class="half">@foreach ($sheet['sender'] as $line)<p class="muted">{{ $line }}</p>@endforeach</td>
            <td class="half">@foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach</td>
        </tr>
    </table>
    <p class="object" style="margin-top: 8px;">{{ $sheet['object'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@elseif ($family === 'bande')
    <div class="band" style="background: {{ $accent }}; color: #fff; padding: 14px 16px;"><h1>{{ $sheet['name'] }}</h1></div>
    <p class="band-object" style="background: #1c1917; color: #fff; padding: 8px 16px;">{{ $sheet['object'] }}</p>
    <div class="pad">
        <p class="muted">{{ implode(' · ', $sheet['sender']) }}</p>
        @foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach
        <p class="muted" style="text-align: right;">{{ $sheet['place'] }}</p>
        <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
        <p class="sign">{{ $sheet['name'] }}</p>
    </div>
@elseif ($family === 'moderne')
    <div class="band" style="background: {{ $accent }}; color: #fff; padding: 14px 16px;"><h1>{{ $sheet['name'] }}</h1></div>
    <div class="pad">
        <table class="sheet-table"><tr><td class="half">@foreach ($sheet['sender'] as $line)<p>{{ $line }}</p>@endforeach</td><td class="half">@foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach</td></tr></table>
        <p class="muted" style="text-align: right; margin-top: 12px;">{{ $sheet['place'] }}</p>
        <p class="object">{{ $sheet['object'] }}</p>
        <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
        <p class="sign">{{ $sheet['name'] }}</p>
    </div>
@elseif ($family === 'bandeau')
    <div class="band" style="background: {{ $accent }}; color: #fff; padding: 14px 16px;"><h1>{{ $sheet['name'] }}</h1></div>
    <p class="band-object" style="background: #1c1917; color: #fff; padding: 8px 16px;">{{ $sheet['object'] }}</p>
    <div class="pad">
        @foreach ($sheet['sender'] as $line)<p class="muted">{{ $line }}</p>@endforeach
        @foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach
        <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
        <p class="sign">{{ $sheet['name'] }}</p>
    </div>
@elseif ($family === 'elegant')
    <h1 style="font-family: DejaVu Serif, serif; font-weight: normal; letter-spacing: 2px; text-transform: uppercase; font-size: 18pt; text-align: center;">{{ $sheet['name'] }}</h1>
    <div class="rule" style="border-top-color: {{ $accent }};"></div>
    <p class="muted" style="text-align: center;">{{ implode(' · ', $sheet['sender']) }}</p>
    <p class="object" style="text-align: center;">{{ $sheet['object'] }}</p>
    @foreach ($sheet['recipient'] as $line)<p style="text-align: center;">{{ $line }}</p>@endforeach
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@else
    <table class="sheet-table">
        <tr>
            <td class="half">@foreach ($sheet['sender'] as $line)<p>{{ $line }}</p>@endforeach</td>
            <td class="half">@foreach ($sheet['recipient'] as $line)<p>{{ $line }}</p>@endforeach</td>
        </tr>
    </table>
    <p class="muted" style="margin-top: 16px; text-align: right;">{{ $sheet['place'] }}</p>
    <p class="object">{{ $sheet['object'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
@endif
