@extends('layouts.app')

@section('content')
<style>
    .reports-page {
        color: #0f172a;
    }

    .reports-heading {
        margin-bottom: 24px;
    }

    .reports-heading h2 {
        font-size: 22px;
        margin: 0;
    }

    .reports-heading p {
        color: #64748b;
        font-size: 13px;
        margin: 4px 0 0;
    }

    .reports-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .report-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .report-tab {
        border: 1px solid transparent;
        color: #475569;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 7px 16px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .report-tab:hover {
        color: #059669;
        background: #ecfdf5;
    }

    .report-tab.active {
        color: #ffffff;
        background: #10b981;
        border-color: #10b981;
    }

    .report-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .report-action {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #0f172a;
        min-height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 7px 14px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .report-action:hover {
        background: #f8fafc;
        color: #047857;
    }

    .report-current {
        margin-bottom: 16px;
    }

    .report-current h3 {
        font-size: 18px;
        margin: 0;
    }

    .report-current p {
        color: #64748b;
        font-size: 13px;
        margin: 4px 0 0;
    }

    .report-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .report-stat {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .report-stat-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        margin: 0 0 8px;
        text-transform: uppercase;
    }

    .report-stat-value {
        color: #0f172a;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.1;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .report-stat-hint {
        color: #94a3b8;
        font-size: 12px;
        margin: 6px 0 0;
    }

    .report-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .report-stat.success .report-stat-icon {
        background: #ecfdf5;
        color: #059669;
    }

    .report-stat.warning .report-stat-icon {
        background: #fffbeb;
        color: #d97706;
    }

    .report-stat.danger .report-stat-icon {
        background: #fff1f2;
        color: #e11d48;
    }

    .report-card {
        background: #ffffff;
        border: 1px solid #dbe4ee;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .report-card-header {
        padding: 20px 20px 10px;
    }

    .report-card-header h3 {
        font-size: 14px;
        margin: 0;
    }

    .report-card-header p {
        color: #64748b;
        font-size: 12px;
        margin: 4px 0 0;
    }

    .report-chart-wrap {
        padding: 0 20px 20px;
    }

    .report-chart {
        min-height: 230px;
        padding: 20px 0 8px;
        border-bottom: 1px solid #edf2f7;
        background-image:
            linear-gradient(to bottom, #e5edf5 1px, transparent 1px);
        background-size: 100% 45px;
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(82px, 1fr);
        gap: 22px;
        align-items: end;
        overflow-x: auto;
    }

    .chart-group {
        display: grid;
        grid-template-rows: 180px auto;
        gap: 8px;
        min-width: 76px;
    }

    .chart-bars {
        display: flex;
        align-items: end;
        justify-content: center;
        gap: 6px;
        height: 180px;
    }

    .chart-bar {
        width: 34px;
        min-height: 0;
        border-radius: 5px 5px 0 0;
        display: block;
        transition: opacity 0.15s ease;
    }

    .chart-bar:hover {
        opacity: 0.82;
    }

    .chart-label {
        color: #64748b;
        font-size: 12px;
        text-align: center;
        white-space: nowrap;
    }

    .chart-empty {
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 13px;
    }

    .report-legend {
        display: flex;
        justify-content: center;
        gap: 14px;
        padding-top: 12px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        display: inline-block;
    }

    .report-table-title {
        padding: 18px 16px;
        border-bottom: 1px solid #dbe4ee;
        font-size: 14px;
        font-weight: 800;
    }

    .report-table-wrap {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
    }

    .report-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        padding: 13px 16px;
        text-align: left;
        text-transform: uppercase;
        border-bottom: 1px solid #dbe4ee;
        white-space: nowrap;
    }

    .report-table td {
        color: #0f172a;
        font-size: 13.5px;
        padding: 13px 16px;
        border-bottom: 1px solid #e5edf5;
        vertical-align: middle;
        white-space: nowrap;
    }

    .report-table tr:last-child td {
        border-bottom: none;
    }

    .cell-tone-success {
        color: #059669 !important;
        font-weight: 800;
    }

    .cell-tone-warning {
        color: #d97706 !important;
        font-weight: 800;
    }

    .cell-tone-danger {
        color: #e11d48 !important;
        font-weight: 800;
    }

    .cell-mono {
        color: #64748b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 1100px) {
        .report-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .reports-toolbar,
        .report-actions,
        .report-tabs {
            align-items: stretch;
            width: 100%;
        }

        .report-tab,
        .report-action {
            flex: 1 1 auto;
        }

        .report-stats {
            grid-template-columns: 1fr;
        }

        .report-chart {
            grid-auto-columns: minmax(70px, 1fr);
            gap: 14px;
        }
    }

    @media print {
        .sidebar,
        .topbar,
        .reports-toolbar,
        .report-actions {
            display: none !important;
        }

        .main {
            margin-left: 0 !important;
        }

        .page-content {
            padding: 0 !important;
        }

        .report-card,
        .report-stat {
            box-shadow: none !important;
            break-inside: avoid;
        }

        .reports-page {
            background: #ffffff;
        }
    }
</style>

<div class="reports-page">
    <div class="reports-heading">
        <h2>Reports</h2>
        <p>Analytics and export for all system modules</p>
    </div>

    <div class="reports-toolbar">
        <div class="report-tabs" aria-label="Report types">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('reports.index', ['report' => $key]) }}"
                    class="report-tab {{ $activeReport === $key ? 'active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="report-actions">
            <button type="button" class="report-action" onclick="window.print()">
                <i class="fa-solid fa-download"></i>
                PDF
            </button>
            <a class="report-action" href="{{ route('reports.export', ['report' => $activeReport]) }}">
                <i class="fa-solid fa-download"></i>
                CSV
            </a>
        </div>
    </div>

    <div class="report-current">
        <h3>{{ $report['title'] }}</h3>
        <p>{{ $report['subtitle'] }}</p>
    </div>

    <div class="report-stats">
        @foreach($report['stats'] as $stat)
            <div class="report-stat {{ $stat['tone'] }}">
                <div>
                    <p class="report-stat-label">{{ $stat['label'] }}</p>
                    <p class="report-stat-value">{{ $stat['value'] }}</p>
                    <p class="report-stat-hint">{{ $stat['hint'] }}</p>
                </div>
                <span class="report-stat-icon">
                    <i class="{{ $stat['icon'] }}"></i>
                </span>
            </div>
        @endforeach
    </div>

    <section class="report-card">
        <div class="report-card-header">
            <h3>{{ $report['chartTitle'] }}</h3>
            <p>{{ $report['subtitle'] }}</p>
        </div>

        <div class="report-chart-wrap">
            @if(count($report['chart']['labels']) > 0)
                <div class="report-chart">
                    @foreach($report['chart']['labels'] as $labelIndex => $label)
                        <div class="chart-group">
                            <div class="chart-bars">
                                @foreach($report['chart']['series'] as $series)
                                    @php
                                        $value = (int) ($series['values'][$labelIndex] ?? 0);
                                        $height = $value > 0 ? max(6, ($value / $report['chart']['max']) * 100) : 0;
                                    @endphp
                                    <span
                                        class="chart-bar"
                                        title="{{ $series['name'] }}: {{ number_format($value) }}"
                                        style="height: {{ $height }}%; background: {{ $series['color'] }};"
                                    ></span>
                                @endforeach
                            </div>
                            <span class="chart-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="report-legend">
                    @foreach($report['chart']['series'] as $series)
                        <span class="legend-item">
                            <span class="legend-dot" style="background: {{ $series['color'] }};"></span>
                            {{ $series['name'] }}
                        </span>
                    @endforeach
                </div>
            @else
                <div class="chart-empty">No chart data available.</div>
            @endif
        </div>
    </section>

    <section class="report-card">
        <div class="report-table-title">Summary Table</div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        @foreach($report['headers'] as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $row)
                        <tr>
                            @foreach($row['cells'] as $cell)
                                <td class="{{ $cell['tone'] ? 'cell-tone-' . $cell['tone'] : '' }} {{ $cell['mono'] ? 'cell-mono' : '' }}">
                                    {{ $cell['value'] }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['headers']) }}" style="text-align:center; padding:40px; color:#94a3b8;">
                                {{ $report['empty'] }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
