@extends('layouts.main')

@section('title', 'Dashboard | PlayFlow POS')
@section('page_title', 'Dashboard สรุปภาพรวม')
@section('page_subtitle', 'ข้อมูลประจำสาขา สุขุมวิท')

@section('content')
<div class="row g-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 opacity-75">ยอดขายรวมวันนี้</h6>
                    <i class="bi bi-currency-dollar fs-4"></i>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($stats['today_sales']) }} ฿</h2>
                <p class="small mb-0"><i class="bi bi-clock-history"></i> {{ $stats['last_sync'] }}</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 text-secondary">
                    <h6 class="mb-0">จำนวนลูกค้าวันนี้</h6>
                    <i class="bi bi-people fs-4"></i>
                </div>
                <h2 class="fw-bold mb-1 text-dark">{{ $stats['today_clients'] }} คน</h2>
                <p class="small mb-0 text-success"><i class="bi bi-arrow-up"></i> +12% จากเมื่อวาน</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-info">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 text-secondary">
                    <h6 class="mb-0">ยอดขายเดือนนี้</h6>
                    <i class="bi bi-graph-up fs-4"></i>
                </div>
                <h2 class="fw-bold mb-1 text-dark">{{ number_format($stats['monthly_sales']) }} ฿</h2>
                <p class="small mb-0 text-muted">เป้าหมาย: 150,000 ฿</p>
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

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">พนักงานทำยอดสูงสุด (Top Earner)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">พนักงาน</th>
                                <th class="border-0 text-center">จำนวนคิว</th>
                                <th class="border-0 text-end">ยอดรวม</th>
                                <th class="border-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['top_masseuses'] as $m)
                            <tr>
                                <td class="border-0">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $m['avatar'] }}" class="rounded-circle me-3" width="40" height="40">
                                        <span class="fw-bold">{{ $m['name'] }}</span>
                                    </div>
                                </td>
                                <td class="border-0 text-center">{{ $m['queue_count'] }} คิว</td>
                                <td class="border-0 text-end fw-bold text-primary">{{ number_format($m['amount']) }} ฿</td>
                                <td class="border-0 text-end">
                                    <span class="badge bg-light text-primary rounded-pill">Top {{ $loop->iteration }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: [12000, 15000, 11000, 18000, 22000, 25000, 28000],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
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