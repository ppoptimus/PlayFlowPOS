@extends('layouts.main')

@section('title', 'Dashboard | PlayFlow Spa POS')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'ภาพรวมสาขาแบบ Real-time (Mock Data)')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <section class="pf-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h3 class="pf-section-title">ยอดขายวันนี้</h3>
                <span class="pf-badge">วันนี้</span>
            </div>
            <div class="display-6 fw-bold text-primary">{{ number_format($stats['today_sales']) }} ฿</div>
            <div class="small text-success fw-semibold">+12% เทียบเมื่อวาน</div>
        </section>
    </div>
    <div class="col-12 col-md-4">
        <section class="pf-card h-100">
            <h3 class="pf-section-title mb-2">ลูกค้าที่รับบริการ</h3>
            <div class="display-6 fw-bold" style="color:#1b8fbc;">{{ $stats['today_clients'] }} คน</div>
            <div class="small text-secondary">Walk-in และ Booking รวมกัน</div>
        </section>
    </div>
    <div class="col-12 col-md-4">
        <section class="pf-card h-100" style="background:linear-gradient(135deg, rgba(49,184,233,.2), rgba(28,201,182,.2));">
            <h3 class="pf-section-title mb-2">ยอดรวมเดือนนี้</h3>
            <div class="display-6 fw-bold" style="color:#17739e;">{{ number_format($stats['monthly_sales']) }} ฿</div>
            <div class="small text-secondary">{{ $stats['last_sync'] }}</div>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <section class="pf-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="pf-section-title">Top Earner หมอนวด</h3>
                <span class="small text-secondary">Top 3</span>
            </div>
            @foreach($stats['top_masseuses'] as $index => $m)
            <article class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:rgba(39,130,175,.15)!important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width:30px;height:30px;background:{{ $index === 0 ? '#1fbba6' : ($index === 1 ? '#31b8e9' : '#5fc5ff') }};">
                        {{ $index + 1 }}
                    </span>
                    <img src="{{ $m['avatar'] }}" alt="{{ $m['name'] }}" width="38" height="38" class="rounded-circle" style="object-fit:cover;">
                    <div>
                        <div class="fw-semibold">{{ $m['name'] }} <span class="small text-secondary">({{ $m['id'] }})</span></div>
                        <div class="small text-secondary">{{ $m['queue_count'] }} คิววันนี้</div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-primary">{{ number_format($m['amount']) }} ฿</div>
                    <div class="small text-secondary">คอมมิชชั่นประมาณ {{ number_format($m['amount'] * 0.3) }} ฿</div>
                </div>
            </article>
            @endforeach
        </section>
    </div>

    <div class="col-12 col-lg-5">
        <section class="pf-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="pf-section-title">บริการขายดี</h3>
                <span class="small text-secondary">รายวัน/รายเดือน</span>
            </div>
            @foreach($stats['top_services'] as $s)
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="fw-semibold">{{ $s['icon'] }} {{ $s['name'] }}</div>
                    <div class="small fw-semibold text-secondary">{{ $s['percent'] }}%</div>
                </div>
                <div class="progress" style="height:10px;border-radius:999px;background:#e8f6fc;">
                    <div class="progress-bar" style="width:{{ $s['percent'] }}%;background:linear-gradient(120deg,#31b8e9,#1cc9b6);"></div>
                </div>
                <div class="small text-secondary mt-1">{{ $s['count'] }} ครั้ง | {{ number_format($s['price']) }} ฿</div>
            </div>
            @endforeach
        </section>
    </div>
</div>
@endsection
