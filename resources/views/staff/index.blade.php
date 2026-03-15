@extends('layouts.main')

@section('title', 'Staff | PlayFlow Spa POS')
@section('page_title', 'Staff Management')
@section('page_subtitle', 'Mockup: โปรไฟล์, กะงาน, รายได้ และคอมมิชชั่น')

@section('content')
<div class="row g-3 mb-3">
    @foreach($staff as $s)
    <div class="col-12 col-md-6 col-xl-4">
        <section class="pf-card h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ $s['avatar'] }}" alt="{{ $s['name'] }}" width="56" height="56" class="rounded-circle" style="object-fit:cover; border:2px solid rgba(45,187,217,.35);">
                <div>
                    <h3 class="pf-section-title mb-0">{{ $s['name'] }}</h3>
                    <div class="small text-secondary">{{ $s['id'] }} • {{ $s['role'] }}</div>
                    <div class="small fw-semibold" style="color:#1f8eaa;">{{ $s['status'] }}</div>
                </div>
            </div>

            <div class="small text-secondary mb-2">Shift: {{ $s['shift'] }}</div>
            <div class="d-flex justify-content-between py-1">
                <span>รายได้วันนี้</span>
                <strong class="text-primary">{{ number_format($s['income']) }} ฿</strong>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span>คอมมิชชั่น (Mock)</span>
                <strong style="color:#148f7b;">{{ number_format($s['commission']) }} ฿</strong>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span>คิวที่ได้รับ</span>
                <strong>{{ count($s['queue']) }} คิว</strong>
            </div>
            <hr>
            <div class="small fw-semibold mb-1">คิวล่าสุด</div>
            @forelse($s['queue'] as $q)
            <div class="small mb-1">
                {{ $q['start'] }}-{{ $q['end'] }} • {{ $q['customer'] }} • {{ $q['service'] }}
            </div>
            @empty
            <div class="small text-secondary">ยังไม่มีคิวในช่วงเวลานี้</div>
            @endforelse
        </section>
    </div>
    @endforeach
</div>

<section class="pf-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="pf-section-title mb-0">ตารางกะงานวันนี้ (Mock)</h3>
        <span class="small text-secondary">10:00 - 20:00</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>พนักงาน</th>
                    <th>เวลาเข้างาน</th>
                    <th>พัก</th>
                    <th>สถานะ</th>
                    <th>Queue Load</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $s)
                <tr>
                    <td class="fw-semibold">{{ $s['name'] }}</td>
                    <td>{{ $s['shift'] }}</td>
                    <td>13:00 - 14:00</td>
                    <td>{{ $s['status'] }}</td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            @php
                                $load = min(100, count($s['queue']) * 30);
                            @endphp
                            <div class="progress-bar" style="width: {{ $load }}%; background:linear-gradient(120deg,#2d8ff0,#14b89a);"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
