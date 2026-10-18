<h1>{{ $sheet['name'] }}</h1>
<p class="muted" style="text-align:center;margin-top:4px;">{{ implode(' · ', $sheet['sender']) }}</p>
<div class="rule"></div>
<table class="sheet-table">
    <tr>
        <td class="half">
            @foreach ($sheet['recipient'] as $line)
                <p>{{ $line }}</p>
            @endforeach
        </td>
        <td class="half" style="text-align:right;">
            <p class="muted">{{ $sheet['place'] }}</p>
            <p class="object">{{ $sheet['object'] }}</p>
        </td>
    </tr>
</table>
<div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
<p class="sign">{{ $sheet['name'] }}</p>
