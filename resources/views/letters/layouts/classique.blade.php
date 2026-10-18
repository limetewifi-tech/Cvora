<table class="sheet-table">
    <tr>
        <td class="half">
            @foreach ($sheet['sender'] as $line)
                <p>{{ $line }}</p>
            @endforeach
        </td>
        <td class="half">
            @foreach ($sheet['recipient'] as $line)
                <p>{{ $line }}</p>
            @endforeach
        </td>
    </tr>
</table>
<p class="muted" style="margin-top:16px;text-align:right;">{{ $sheet['place'] }}</p>
<p class="object">{{ $sheet['object'] }}</p>
<div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
<p class="sign">{{ $sheet['name'] }}</p>
