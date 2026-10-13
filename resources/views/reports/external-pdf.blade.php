<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #4b5563; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .stats { margin-top: 14px; }
        .stats td { width: 25%; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <p>{{ $report['subtitle'] }}</p>

    @if (!empty($report['stats']))
        <table class="stats">
            <tbody>
                @foreach (array_chunk($report['stats'], 4) as $statRow)
                    <tr>
                        @foreach ($statRow as $stat)
                            <td><strong>{{ $stat['label'] }}</strong><br>{{ $stat['value'] }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table>
        <thead>
            <tr>
                @foreach ($report['headers'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row['csv'] as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($report['headers']), 1) }}">{{ $report['empty'] }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
