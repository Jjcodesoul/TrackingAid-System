@extends('layouts.app')

@section('content')
<style>
    /* Spacing and Alignment Structure */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 32px;
        width: 100%;
    }

    .dashboard-header {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 20px;
    }

    .dashboard-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .dashboard-header p {
        font-size: 14px;
        color: #718096;
    }

    /* Grid layout for the upper 3 metrics cards */
    .metric-card-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 768px) {
        .metric-card-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Elegant Custom Metric Card Styling */
    .custom-metric-card {
        background: #ffffff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .metric-info span.title {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #a0aec0;
    }

    .metric-info h3 {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-dark);
        margin-top: 8px;
        margin-bottom: 6px;
    }

    .metric-info span.trend {
        font-size: 12px;
        font-weight: 500;
    }

    .metric-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
    }

    /* Color backgrounds matching your app palette */
    .icon-blue { background-color: #ebf8ff; color: #3182ce; }
    .icon-green { background-color: #f0fff4; color: #38a169; }
    .icon-amber { background-color: #fffaf0; color: #dd6b20; }

    /* Secondary Layout Section Split (Chart vs Overview) */
    .content-layout-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 1024px) {
        .content-layout-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    .panel-card {
        background: #ffffff;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .panel-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .panel-header i {
        color: #a0aec0;
    }

    .chart-placeholder {
        height: 260px;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f7fafc;
        color: #a0aec0;
        font-size: 14px;
        gap: 8px;
    }

    /* Progress Indicators for Status Bars */
    .progress-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .progress-item-header {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .status-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4a5568;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .progress-track {
        width: 100%;
        background-color: #edf2f7;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
    }

    .panel-footer {
        font-size: 12px;
        color: #a0aec0;
        border-top: 1px solid #edf2f7;
        padding-top: 16px;
        margin-top: 24px;
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>Dashboard</h2>
        <p>Track and manage your disaster logistics layout metrics across operations.</p>
    </div>

    <div class="metric-card-grid">
        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="title">Total Inventory</span>
                <h3>0</h3>
                <span class="trend" style="color: #38a169;"><i class="fa-solid fa-arrow-trend-up"></i> +12.5% from last month</span>
            </div>
            <div class="metric-icon-wrapper icon-blue">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="title">Active Requests</span>
                <h3>0</h3>
                <span class="trend" style="color: #a0aec0;">From ResqOperation</span>
            </div>
            <div class="metric-icon-wrapper icon-green">
                <i class="fa-solid fa-hand-holding-hand"></i>
            </div>
        </div>

        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="title">Low Stock Alerts</span>
                <h3 style="color: #e53e3e;">0</h3>
                <span class="trend" style="color: #dd6b20;"><i class="fa-solid fa-triangle-exclamation"></i> Requires attention</span>
            </div>
            <div class="metric-icon-wrapper icon-amber">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <div class="content-layout-grid">
        <div class="panel-card">
            <div class="panel-header">
                <i class="fa-solid fa-chart-line"></i>
                <span>Inventory Trend</span>
            </div>
            <div class="chart-placeholder">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 18px;"></i>
                <span>Chart data loading from system model...</span>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Request Status Overview</span>
            </div>
            
            <div class="progress-wrapper">
                <div>
                    <div class="progress-item-header">
                        <span class="status-label"><span class="dot" style="background-color: #dd6b20;"></span>Pending</span>
                        <span style="font-weight: 700; color: var(--text-dark);">15</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar" style="width: 26%; background-color: #dd6b20;"></div>
                    </div>
                </div>
                
                <div>
                    <div class="progress-item-header">
                        <span class="status-label"><span class="dot" style="background-color: #38a169;"></span>Approved</span>
                        <span style="font-weight: 700; color: var(--text-dark);">42</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar" style="width: 74%; background-color: #38a169;"></div>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <i class="fa-solid fa-circle-info"></i> Updated in real time via internal systems.
            </div>
        </div>
    </div>
</div>
@endsection