<div class="band">
    <h1>{{ $sheet['name'] }}</h1>
    <p>{{ $sheet['object'] }}</p>
</div>
<div class="pad">
    <table class="sheet-table">
        <tr>
            <td class="half">
                @foreach ($sheet['sender'] as $line)
                    <p class="muted">{{ $line }}</p>
                @endforeach
            </td>
            <td class="half">
                @foreach ($sheet['recipient'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
            </td>
        </tr>
    </table>
    <p class="muted" style="margin-top:14px;text-align:right;">{{ $sheet['place'] }}</p>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
</div>
