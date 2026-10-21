<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }} - TrackingAid</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 8px; }
        .masthead { border-bottom: 2px solid #176b57; padding-bottom: 9px; margin-bottom: 12px; }
        .brand { color: #176b57; font-size: 10px; font-weight: bold; letter-spacing: .5px; }
        h1 { color: #111827; font-size: 18px; margin: 5px 0 3px; }
        .subtitle { color: #4b5563; font-size: 9px; margin: 0; }
        .metadata { margin-top: 8px; color: #4b5563; line-height: 1.6; }
        .stats { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .stats td { width: 25%; border: 1px solid #dbe4ee; background: #f8fafc; padding: 7px 9px; }
        .stat-label { color: #64748b; font-size: 7px; text-transform: uppercase; }
        .stat-value { color: #111827; font-size: 13px; font-weight: bold; margin-top: 3px; }
        .stat-hint { color: #64748b; font-size: 7px; margin-top: 2px; }
        .section-title { color: #111827; font-size: 10px; font-weight: bold; margin: 13px 0 6px; }
        table.report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report thead { display: table-header-group; }
        .report th, .report td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        .report th { background: #eaf2ef; color: #23463d; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .report td { font-size: 7.5px; }
        .report tr { page-break-inside: avoid; }
        .empty { color: #64748b; text-align: center !important; padding: 18px !important; }
        .footer { border-top: 1px solid #dbe4ee; color: #64748b; font-size: 7px; margin-top: 12px; padding-top: 5px; }
    </style>
</head>
<body>
    <header class="masthead">
        <div class="brand">TRACKINGAID · DISASTER RESPONSE LOGISTICS</div>
        <h1>{{ $report['title'] }}</h1>
        <p class="subtitle">{{ $report['subtitle'] }}</p>
        <div class="metadata">
            <strong>Report type:</strong> {{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $reportType)) }}
            &nbsp; · &nbsp; <strong>Generated:</strong> {{ $generatedAt->format('M d, Y H:i') }}
            <br><strong>Period:</strong> {{ $filters['start_date'] ?? 'Earliest available' }} to {{ $filters['end_date'] ?? 'Today' }}
            @if(!empty($filters['category'])) &nbsp; · &nbsp; <strong>Category:</strong> {{ $filters['category'] }}@endif
            @if(!empty($filters['status'])) &nbsp; · &nbsp; <strong>Status:</strong> {{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $filters['status'])) }}@endif
        </div>
    </header>

    @if(!empty($report['stats']))
        <table class="stats"><tbody>
            @foreach(array_chunk($report['stats'], 4) as $statRow)
                <tr>
                    @foreach($statRow as $stat)
                        <td><div class="stat-label">{{ $stat['label'] }}</div><div class="stat-value">{{ $stat['value'] }}</div><div class="stat-hint">{{ $stat['hint'] }}</div></td>
                    @endforeach
                    @for($i = count($statRow); $i < 4; $i++)<td></td>@endfor
                </tr>
            @endforeach
        </tbody></table>
    @endif

    <div class="section-title">Full report table</div>
    <table class="report">
        <thead><tr>@foreach($report['headers'] as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>@foreach($row['cells'] as $cell)<td>{{ $cell['value'] }}</td>@endforeach</tr>
            @empty
                <tr><td class="empty" colspan="{{ max(count($report['headers']), 1) }}">{{ $report['empty'] ?? 'No records are available for the selected filters.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">TrackingAid · {{ $report['title'] }} · Generated {{ $generatedAt->format('M d, Y H:i') }}</div>
</body>
</html>
