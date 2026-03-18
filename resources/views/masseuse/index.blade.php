@extends('layouts.main')

@section('title', 'Masseuse | PlayFlow Spa POS')
@section('page_title', 'Masseuse Management')
@section('page_subtitle', 'จัดการข้อมูลหมอนวด')

@php
    $splitDisplayName = static function (string $name): array {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        if (count($parts) <= 1) {
            return [$name, ''];
        }

        $firstName = array_shift($parts);
        return [$firstName ?? $name, implode(' ', $parts)];
    };

    $totalIncome = collect($staff ?? [])->sum('income');
    $totalCommission = collect($staff ?? [])->sum('commission');
    $totalQueue = collect($staff ?? [])->sum(static function (array $item): int {
        return count($item['queue'] ?? []);
    });
    $workingTodayCount = collect($staff ?? [])->where('isWorkingToday', true)->count();
    $createUrl = route('masseuse.create', array_filter([
        'branch_id' => $activeBranchId ?? null,
        'date' => $selectedDate ?? null,
    ], static function ($value): bool {
        return $value !== null && $value !== '';
    }));
@endphp

@push('head')
@include('masseuse.partials.styles')
@endpush

@section('content')
<div class="row g-3 masseuse-page">
    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-0">
            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="col-12">
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-0">
            <div class="fw-bold mb-1">บันทึกข้อมูลไม่สำเร็จ</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if(!($moduleReady ?? false))
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-0">
            <div class="fw-bold mb-1">ยังไม่พบตารางหมอนวดในฐานข้อมูล</div>
            <div>หน้านี้ต้องใช้ตาราง <code>masseuses</code> เพื่อแสดงและจัดการข้อมูลหมอนวด</div>
        </div>
    </div>
    @else
    <div class="col-12">
        <section class="hero-card p-3 p-lg-4">
            <div class="row g-3 align-items-end position-relative">
                <div class="col-12 col-xl-5">
                    <div class="hero-title">โมดูลหมอนวด</div>
                    <p class="hero-subtitle mb-0 mt-2">
                        สรุปรายได้ คอมมิชชั่น และคิวงาน พร้อมแยกหน้าเพิ่มหมอนวดและหน้าแก้ไขข้อมูลให้ทำงานชัดเจนขึ้น
                    </p>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <div class="hero-metric">
                                <span class="hero-metric-label">หมอนวดทั้งหมด</span>
                                <div class="hero-metric-value">{{ number_format(count($staffRecords ?? [])) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-metric">
                                <span class="hero-metric-label">มาทำงานวันนี้</span>
                                <div class="hero-metric-value">{{ number_format($workingTodayCount) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-metric">
                                <span class="hero-metric-label">รายได้รวม</span>
                                <div class="hero-metric-value">{{ number_format($totalIncome) }} ฿</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-metric">
                                <span class="hero-metric-label">คอมรวม / คิวรวม</span>
                                <div class="hero-metric-value">{{ number_format($totalCommission) }} ฿ / {{ number_format($totalQueue) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12">
        <div class="section-header mb-1">
            <div>
                <h3 class="section-title">สรุปผลงานรายคน</h3>
                <div class="section-subtitle">ดึงจากคิวในวันที่ {{ $selectedDate }}</div>
            </div>
            @if($canManage)
            <a href="{{ $createUrl }}" class="btn btn-primary section-action-btn page-action">
                <span class="section-action-icon"><i class="fa-solid fa-plus"></i></span>
                <span class="section-action-label">เพิ่มหมอนวด</span>
            </a>
            @else
            <div class="helper-text"></div>
            @endif
        </div>
    </div>

    @forelse($staff as $s)
    @php
        [$firstName, $lastName] = $splitDisplayName((string) $s['name']);
        $latestQueue = $s['queue'][0] ?? null;
        $statusClass = !empty($s['isWorkingToday'])
            ? (!empty($s['queue']) ? 'is-success' : 'is-warning')
            : 'is-muted';
        $editUrl = route('masseuse.edit', array_filter([
            'staffId' => $s['id'],
            'branch_id' => $activeBranchId ?? null,
            'date' => $selectedDate ?? null,
        ], static function ($value): bool {
            return $value !== null && $value !== '';
        }));
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <section class="staff-card{{ $s['isWorkingToday'] ? '' : ' is-off-duty' }}">
            <div class="staff-toolbar">
                <div class="staff-head">
                    <img src="{{ $s['avatar'] }}" alt="{{ $s['name'] }}" class="staff-avatar">
                    <div class="min-w-0">
                        <div class="staff-name">
                            <span>{{ $firstName }}</span>
                            @if($lastName !== '')
                            <span>{{ $lastName }}</span>
                            @endif
                        </div>
                        <div class="staff-id">{{ $s['display_id'] }}</div>
                    </div>
                </div>

                @if($canManage)
                <a href="{{ $editUrl }}" class="btn btn-outline-primary staff-edit-btn" aria-label="แก้ไขข้อมูลหมอนวด" title="แก้ไขข้อมูลหมอนวด">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                @endif
            </div>

            <div class="mt-3">
                <span class="status-pill {{ $statusClass }}">{{ $s['status'] }}</span>
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-label">รายได้วันนี้</span>
                    <div class="stat-value">{{ number_format($s['income']) }} ฿</div>
                </div>
                <div class="stat-box">
                    <span class="stat-label">คอมมิชชั่น</span>
                    <div class="stat-value">{{ number_format($s['commission']) }} ฿</div>
                </div>
                <div class="stat-box">
                    <span class="stat-label">คิวที่ได้รับ</span>
                    <div class="stat-value">{{ count($s['queue']) }} คิว</div>
                </div>
            </div>

            <div class="queue-box">
                <div class="queue-title">คิวล่าสุด</div>
                @if($latestQueue)
                <span class="queue-meta queue-time">{{ $latestQueue['start'] }} - {{ $latestQueue['end'] }}</span>
                <span class="queue-meta">{{ $latestQueue['customer'] }}</span>
                <span class="queue-meta">{{ $latestQueue['service'] }}</span>
                @else
                <span class="queue-meta">ยังไม่มีคิวในวันที่เลือก</span>
                @endif
            </div>
        </section>
    </div>
    @empty
    <div class="col-12">
        <div class="empty-state">
            <div class="fw-bold mb-1">ยังไม่มีข้อมูลหมอนวด</div>
            <div>{{ $canManage ? 'เริ่มต้นโดยกดปุ่มเพิ่มหมอนวดด้านบน' : 'ยังไม่มีรายการสำหรับสาขานี้' }}</div>
        </div>
    </div>
    @endforelse

    <div class="col-12 pt-2">
        <section class="attendance-card">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <h3 class="section-title">สถานะมาทำงานวันนี้</h3>
                    <div class="section-subtitle">ใช้สำหรับเปิดหรือปิดการพร้อมรับงานรายวันของหมอนวด</div>
                </div>
                <div class="helper-text">ยังไม่ใช่ shift management เต็มรูปแบบ</div>
            </div>

            <div class="table-responsive">
                <table class="table attendance-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="attendance-col"></th>
                            <th>หมอนวด</th>
                            <th style="min-width: 180px;">สถานะ</th>
                            <th style="min-width: 170px;">Queue Load</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $s)
                        <tr class="{{ $s['isWorkingToday'] ? '' : 'attendance-row-off' }}">
                            <td class="attendance-col">
                                <form method="POST" action="{{ route('masseuse.attendance') }}">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <input type="hidden" name="branch_id" value="{{ $activeBranchId }}">
                                    <input type="hidden" name="staff_id" value="{{ $s['id'] }}">
                                    <input type="hidden" name="is_working" value="0">
                                    <label class="toggle">
                                        <input
                                            type="checkbox"
                                            name="is_working"
                                            value="1"
                                            {{ $s['isWorkingToday'] ? 'checked' : '' }}
                                            onchange="this.form.submit()"
                                        >
                                        <span class="toggle-track">
                                            <span class="toggle-thumb"></span>
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="fw-semibold">{{ $s['name'] }}</td>
                            <td>{{ $s['status'] }}</td>
                            <td class="queue-load">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $s['queueLoad'] }}%; background:linear-gradient(120deg,#2d8ff0,#14b89a);"></div>
                                </div>
                                <div class="helper-text mt-1">{{ count($s['queue']) }} คิว</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลหมอนวด</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    @endif
</div>
@endsection
