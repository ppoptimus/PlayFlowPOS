@extends('layouts.main')

@section('title', 'Dashboard | PlayFlow POS')
@section('page_title', 'Dashboard')
@section('page_subtitle', $stats['branch_name'] ?? '-')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card dashboard-card section-surface">
            <div class="card-body p-4 p-lg-5">

                <div class="row g-3">
                    <div class="col-6 col-md-6">
                        <div class="card dashboard-card h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold text-secondary mb-3 section-subtitle">ลูกค้าวันนี้</h5>
                                <h1 class="fw-bold mb-2 text-dark stat-big">{{ $stats['today_clients'] }} คน</h1>
                                <p class="small mb-0 {{ $stats['client_trend']['class'] ?? 'text-secondary' }}">
                                    <i class="bi {{ $stats['client_trend']['icon'] ?? 'bi-arrow-right' }}"></i>
                                    {{ $stats['client_trend']['text'] ?? '0% จากเมื่อวาน' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="card dashboard-card h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                                <i class="bi bi-receipt fs-1 text-primary mb-2"></i>
                                <h2 class="fw-bold mb-0 section-title">{{ number_format($stats['today_orders'] ?? 0) }}</h2>
                                <p class="mb-0 fw-semibold text-secondary">บิลวันนี้</p>
                                <small class="text-muted">อัปเดต {{ $stats['last_sync'] ?? '-' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card dashboard-card h-100">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                    <h4 class="fw-bold mb-0 section-title">รายงานยอดขาย</h4>
                    <div class="btn-group report-range-group" role="group" aria-label="ช่วงเวลารายงานยอดขาย">
                        <a href="{{ route('dashboard', array_filter(['range' => 'today', 'branch_id' => request('branch_id')])) }}"
                           class="btn report-range-btn {{ ($stats['selected_range'] ?? 'today') === 'today' ? 'active' : '' }}">วันนี้</a>
                        <a href="{{ route('dashboard', array_filter(['range' => 'yesterday', 'branch_id' => request('branch_id')])) }}"
                           class="btn report-range-btn {{ ($stats['selected_range'] ?? 'today') === 'yesterday' ? 'active' : '' }}">เมื่อวาน</a>
                        <a href="{{ route('dashboard', array_filter(['range' => '7d', 'branch_id' => request('branch_id')])) }}"
                           class="btn report-range-btn {{ ($stats['selected_range'] ?? 'today') === '7d' ? 'active' : '' }}">7 วันย้อนหลัง</a>
                    </div>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="report-row rounded-4 px-4 py-3">
                        <span class="report-label">{{ $stats['selected_range_label'] ?? 'วันนี้' }}</span>
                        <span class="report-value">{{ number_format($stats['selected_range_sales'] ?? 0) }} บ.</span>
                    </div>
                    <div class="report-row rounded-4 px-4 py-3">
                        <span class="report-label">รายเดือน</span>
                        <span class="report-value">{{ number_format($stats['monthly_sales']) }} บ.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card dashboard-card h-100">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-4 section-title">ค่ามือหมอนวด</h4>
                <div class="d-flex flex-column gap-3">
                    <div class="report-row rounded-4 px-4 py-3">
                        <span class="report-label">รายวัน</span>
                        <span class="report-value">{{ number_format($stats['daily_masseuse_fee'] ?? 0) }} บ.</span>
                    </div>
                    <div class="report-row rounded-4 px-4 py-3">
                        <span class="report-label">รายเดือน</span>
                        <span class="report-value">{{ number_format($stats['monthly_masseuse_fee'] ?? 0) }} บ.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">แนวโน้มยอดขายรายสัปดาห์</h5>
                <canvas id="salesChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">บริการยอดฮิต</h5>
                <div class="d-flex flex-column gap-3">
                    @foreach($stats['top_services'] as $service)
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold">{{ $service['icon'] }} {{ $service['name'] }}</span>
                            <span class="small text-muted">{{ $service['count'] }} ครั้ง</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-primary rounded-pill" style="width: {{ $service['percent'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('head')
<style>
    .dashboard-card {
        border: 1px solid rgba(23, 107, 183, 0.08);
        box-shadow: 0 10px 24px rgba(19, 75, 137, 0.08);
        border-radius: 1rem;
    }
    .section-surface {
        background: linear-gradient(160deg, #f8fdff 0%, #eff8fc 60%, #f4fbf7 100%);
    }
    .section-title {
        font-size: clamp(1.4rem, 1.2rem + 1.1vw, 2.1rem);
        line-height: 1.2;
        color: #143d6b;
    }
    .section-subtitle {
        font-size: clamp(1rem, 0.95rem + 0.5vw, 1.25rem);
    }
    .stat-big {
        font-size: clamp(1.8rem, 1.3rem + 2vw, 3rem);
        line-height: 1.1;
        word-break: break-word;
    }
    .report-range-group {
        background: linear-gradient(145deg, #f7fbff 0%, #edf4fb 58%, #e9f2f9 100%);
        border: 1px solid rgba(24, 76, 132, 0.16);
        border-radius: 999px;
        padding: 0.3rem;
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8), 0 6px 14px rgba(20, 74, 126, 0.08);
    }
    .report-range-btn {
        border: 1px solid transparent;
        color: #2b4f73;
        background: transparent;
        border-radius: 999px !important;
        font-weight: 700;
        padding: 0.42rem 1.05rem;
        transition: all 0.2s ease;
    }
    .report-range-btn:hover {
        color: #1c4168;
        border-color: rgba(27, 93, 157, 0.3);
        background: rgba(26, 92, 156, 0.08);
        transform: translateY(-1px);
    }
    .report-range-btn.active {
        background: linear-gradient(135deg, #1a4f87 0%, #2676bf 52%, #129982 100%);
        color: #ffffff;
        border-color: rgba(19, 69, 115, 0.8);
        box-shadow: 0 8px 18px rgba(16, 62, 107, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.28);
    }
    .report-range-btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 0.22rem rgba(35, 120, 193, 0.25);
    }
    .report-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.4rem 1rem;
        background: linear-gradient(135deg, #9de6f8 0%, #72cae8 45%, #53c9b0 100%);
        color: #103c66;
    }
    .report-label,
    .report-value {
        font-weight: 700;
        font-size: clamp(1.4rem, 1.05rem + 1.25vw, 2.2rem);
        line-height: 1.15;
        min-width: 0;
    }
    .report-value {
        margin-left: auto;
        text-align: right;
        word-break: break-word;
    }
    @media (max-width: 1399.98px) {
        .col-lg-4 .report-label,
        .col-lg-4 .report-value {
            font-size: clamp(1.15rem, 0.95rem + 0.95vw, 1.85rem);
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChartLabels = @json($stats['sales_chart']['labels'] ?? []);
    const salesChartData = @json($stats['sales_chart']['data'] ?? []);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesChartLabels.length ? salesChartLabels : ['-', '-', '-', '-', '-', '-', '-'],
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: salesChartData.length ? salesChartData : [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#1f73e0',
                backgroundColor: 'rgba(31, 115, 224, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endpush

