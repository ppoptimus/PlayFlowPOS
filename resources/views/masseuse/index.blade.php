@extends('layouts.main')

@section('title', 'Masseuse | PlayFlow Spa POS')
@section('page_title', 'Masseuse Management')
@section('page_subtitle', 'สุขุมวิท | Manager')

@php
    $splitDisplayName = static function (string $name): array {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        if (count($parts) <= 1) {
            return [$name, ''];
        }

        $firstName = array_shift($parts);
        return [$firstName ?? $name, implode(' ', $parts)];
    };
@endphp

@section('content')
<div class="row g-3 mb-3 staff-grid">
    @foreach($staff as $s)
    @php
        [$firstName, $lastName] = $splitDisplayName((string) $s['name']);
        $latestQueue = $s['queue'][0] ?? null;
    @endphp
    <div class="col-6 col-md-6 col-xl-4">
        <section class="pf-card staff-card h-100{{ $s['isWorkingToday'] ? '' : ' is-off-duty' }}">
            <div class="staff-card-header d-flex align-items-center gap-2 mb-3">
                <img src="{{ $s['avatar'] }}" alt="{{ $s['name'] }}" width="56" height="56" class="rounded-circle staff-avatar">
                <div class="staff-card-main min-w-0">
                    <h3 class="pf-section-title staff-name mb-0">
                        <span class="staff-name-line">{{ $firstName }}</span>
                        @if($lastName !== '')
                        <span class="staff-name-line">{{ $lastName }}</span>
                        @endif
                    </h3>
                    <div class="small text-secondary staff-meta">{{ $s['display_id'] }}</div>
                    <div class="small fw-semibold staff-status">{{ $s['status'] }}</div>
                </div>
            </div>

            <div class="staff-stats">
                <div class="staff-stat-item">
                    <span>รายได้วันนี้</span>
                    <strong class="text-primary">{{ number_format($s['income']) }} ฿</strong>
                </div>
                <div class="staff-stat-item">
                    <span>คอมมิชชั่น</span>
                    <strong class="staff-commission">{{ number_format($s['commission']) }} ฿</strong>
                </div>
                <div class="staff-stat-item staff-stat-item-wide">
                    <span>คิวที่ได้รับ</span>
                    <strong>{{ count($s['queue']) }} คิว</strong>
                </div>
            </div>

            <hr class="staff-divider">

            <div class="small fw-semibold mb-2 staff-queue-title">คิวล่าสุด</div>
            <div class="staff-queue-list">
                @if($latestQueue)
                <div class="small staff-queue-item">
                    <span class="staff-queue-time">{{ $latestQueue['start'] }} - {{ $latestQueue['end'] }}</span>
                    <span class="staff-queue-customer">{{ $latestQueue['customer'] }}</span>
                    <span class="staff-queue-service">{{ $latestQueue['service'] }}</span>
                </div>
                @else
                <div class="small text-secondary staff-queue-empty">
                    <span>ยังไม่มีคิวในช่วงเวลานี้</span>
                </div>
                @endif
            </div>
        </section>
    </div>
    @endforeach
</div>

<section class="pf-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="pf-section-title mb-0">ตารางกะงานวันนี้</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle staff-shift-table">
            <thead>
                <tr>
                    <th class="staff-attendance-col"></th>
                    <th>หมอนวด</th>
                    <th>Queue Load</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $s)
                @php
                    [$firstName, $lastName] = $splitDisplayName((string) $s['name']);
                @endphp
                <tr class="{{ $s['isWorkingToday'] ? '' : 'staff-table-row-off-duty' }}">
                    <td class="staff-attendance-col">
                        <form method="POST" action="{{ route('masseuse.attendance') }}" class="staff-attendance-form">
                            @csrf
                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                            <input type="hidden" name="branch_id" value="{{ $activeBranchId }}">
                            <input type="hidden" name="staff_id" value="{{ $s['id'] }}">
                            <input type="hidden" name="is_working" value="0">
                            <label class="staff-toggle staff-toggle-compact">
                                <input
                                    type="checkbox"
                                    name="is_working"
                                    value="1"
                                    {{ $s['isWorkingToday'] ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                >
                                <span class="staff-toggle-track">
                                    <span class="staff-toggle-thumb"></span>
                                </span>
                            </label>
                        </form>
                    </td>
                    <td class="fw-semibold">
                        <div class="staff-table-name">
                            <span class="staff-name-line">{{ $firstName }}</span>
                            @if($lastName !== '')
                            <span class="staff-name-line">{{ $lastName }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="staff-load-cell">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ $s['queueLoad'] }}%; background:linear-gradient(120deg,#2d8ff0,#14b89a);"></div>
                        </div>
                        <div class="staff-load-text">{{ count($s['queue']) }} คิว</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('head')
<style>
    .pf-card {
        border: 1px solid rgba(31, 115, 224, 0.08);
        border-radius: 1.25rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 250, 255, 0.96));
        box-shadow: 0 14px 32px rgba(31, 78, 138, 0.08);
        padding: 1.1rem;
    }
    .pf-section-title {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.05;
        color: #234262;
    }
    .staff-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .staff-card.is-off-duty {
        background: linear-gradient(180deg, rgba(243, 245, 248, 0.98), rgba(233, 238, 243, 0.96));
        border-color: rgba(142, 154, 170, 0.16);
        box-shadow: 0 10px 24px rgba(78, 96, 118, 0.08);
    }
    .staff-card.is-off-duty .staff-avatar {
        filter: grayscale(1);
        opacity: 0.78;
        border-color: rgba(151, 162, 176, 0.3);
    }
    .staff-card.is-off-duty .staff-name,
    .staff-card.is-off-duty .staff-status,
    .staff-card.is-off-duty .staff-stat-item strong,
    .staff-card.is-off-duty .staff-queue-title {
        color: #647487 !important;
    }
    .staff-card.is-off-duty .staff-meta,
    .staff-card.is-off-duty .staff-stat-item span,
    .staff-card.is-off-duty .staff-queue-item,
    .staff-card.is-off-duty .staff-queue-empty,
    .staff-card.is-off-duty .staff-load-text {
        color: #7e8c9b !important;
    }
    .staff-card.is-off-duty .staff-stat-item,
    .staff-card.is-off-duty .staff-queue-item,
    .staff-card.is-off-duty .staff-queue-empty {
        background: rgba(241, 244, 247, 0.95);
        border-color: rgba(126, 140, 155, 0.12);
    }
    .staff-card-header {
        display: grid !important;
        grid-template-columns: auto minmax(0, 1fr);
        align-items: flex-start !important;
        min-height: 5.25rem;
    }
    .staff-avatar {
        object-fit: cover;
        border: 2px solid rgba(45, 187, 217, 0.35);
        flex-shrink: 0;
    }
    .staff-card-main {
        min-width: 0;
    }
    .staff-name {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.08rem;
        min-height: 2.7rem;
        font-size: clamp(1rem, 1vw + 0.92rem, 1.45rem);
        word-break: break-word;
    }
    .staff-name-line {
        display: block;
        line-height: 1.02;
    }
    .staff-meta {
        font-size: 0.82rem;
        line-height: 1.25;
        word-break: break-word;
        min-height: 2rem;
    }
    .staff-status {
        color: #1f8eaa;
        min-height: 1.2rem;
    }
    .staff-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }
    .staff-stat-item {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.2rem;
        min-height: 4rem;
        padding: 0.7rem 0.8rem;
        border-radius: 1rem;
        background: rgba(239, 247, 255, 0.92);
        border: 1px solid rgba(31, 115, 224, 0.08);
    }
    .staff-stat-item span {
        font-size: 0.76rem;
        color: #6a7f94;
        line-height: 1.2;
    }
    .staff-stat-item strong {
        font-size: 1.05rem;
        line-height: 1;
        color: #234262;
        word-break: break-word;
    }
    .staff-commission {
        color: #148f7b;
    }
    .staff-stat-item-wide {
        grid-column: 1 / -1;
        min-height: 3.65rem;
    }
    .staff-divider {
        margin: 0.95rem 0 0.75rem;
        border-color: rgba(35, 66, 98, 0.12);
    }
    .staff-queue-title {
        letter-spacing: 0.01em;
    }
    .staff-queue-list {
        display: flex;
        flex: 1 1 auto;
    }
    .staff-queue-item,
    .staff-queue-empty {
        width: 100%;
        min-height: 4.8rem;
        padding: 0.58rem 0.7rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(31, 115, 224, 0.08);
        background: rgba(250, 252, 255, 0.95);
        color: #395675;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.15rem;
    }
    .staff-queue-empty {
        background: rgba(250, 252, 255, 0.85);
    }
    .staff-queue-time {
        font-weight: 700;
        color: #234262;
    }
    .staff-queue-customer,
    .staff-queue-service {
        line-height: 1.25;
        word-break: break-word;
    }
    .staff-shift-table th,
    .staff-shift-table td {
        vertical-align: middle;
    }
    .staff-attendance-col {
        width: 1%;
        white-space: nowrap;
        text-align: center;
    }
    .staff-attendance-form {
        margin: 0;
        flex-shrink: 0;
    }
    .staff-table-name {
        display: flex;
        flex-direction: column;
        gap: 0.08rem;
        line-height: 1.1;
        word-break: break-word;
    }
    .staff-load-cell {
        min-width: 100px;
    }
    .staff-load-text {
        margin-top: 0.4rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #5e738b;
    }
    .staff-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        cursor: pointer;
        user-select: none;
    }
    .staff-toggle-compact {
        gap: 0;
    }
    .staff-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .staff-toggle-track {
        width: 2.8rem;
        height: 1.6rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #c4d3e2, #d7e1ea);
        position: relative;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: inset 0 1px 3px rgba(29, 61, 94, 0.16);
        flex-shrink: 0;
    }
    .staff-toggle-thumb {
        position: absolute;
        top: 0.16rem;
        left: 0.18rem;
        width: 1.28rem;
        height: 1.28rem;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 3px 8px rgba(20, 55, 90, 0.18);
        transition: transform 0.2s ease;
    }
    .staff-toggle input:checked + .staff-toggle-track {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        box-shadow: inset 0 1px 3px rgba(17, 88, 118, 0.22);
    }
    .staff-toggle input:checked + .staff-toggle-track .staff-toggle-thumb {
        transform: translateX(1.18rem);
    }
    .staff-table-row-off-duty {
        color: #8d99a8;
    }
    .staff-table-row-off-duty td {
        background-color: rgba(239, 243, 246, 0.65) !important;
    }
    .staff-table-row-off-duty .progress {
        opacity: 0.45;
    }
    @media (max-width: 991.98px) {
        .staff-grid {
            --bs-gutter-x: 0.8rem;
            --bs-gutter-y: 0.8rem;
        }
        .staff-card {
            padding: 0.85rem;
            border-radius: 1rem;
            min-height: 100%;
        }
        .staff-avatar {
            width: 46px;
            height: 46px;
        }
        .staff-card-header {
            min-height: 4.8rem;
            margin-bottom: 0.75rem !important;
        }
        .staff-name {
            font-size: 1rem;
            min-height: 2.35rem;
        }
        .staff-meta,
        .staff-status,
        .staff-queue-item,
        .staff-queue-empty {
            font-size: 0.73rem !important;
        }
        .staff-meta {
            min-height: 1.8rem;
        }
        .staff-toggle-track {
            width: 2.45rem;
            height: 1.4rem;
        }
        .staff-toggle-thumb {
            top: 0.13rem;
            left: 0.15rem;
            width: 1.12rem;
            height: 1.12rem;
        }
        .staff-toggle input:checked + .staff-toggle-track .staff-toggle-thumb {
            transform: translateX(1.02rem);
        }
        .staff-stats {
            gap: 0.45rem;
        }
        .staff-stat-item {
            min-height: 3.6rem;
            padding: 0.55rem 0.6rem;
            border-radius: 0.85rem;
        }
        .staff-stat-item span {
            font-size: 0.65rem;
        }
        .staff-stat-item strong {
            font-size: 0.92rem;
        }
        .staff-divider {
            margin: 0.75rem 0 0.65rem;
        }
        .staff-queue-item,
        .staff-queue-empty {
            min-height: 4.25rem;
            padding: 0.48rem 0.55rem;
            line-height: 1.28;
        }
    }
    @media (max-width: 575.98px) {
        .staff-grid {
            --bs-gutter-x: 0.65rem;
            --bs-gutter-y: 0.65rem;
        }
        .staff-card {
            padding: 0.72rem;
            border-radius: 0.95rem;
        }
        .staff-card-header {
            grid-template-columns: 40px minmax(0, 1fr);
            gap: 0.55rem !important;
            min-height: 4.3rem;
            margin-bottom: 0.6rem !important;
        }
        .staff-avatar {
            width: 40px;
            height: 40px;
        }
        .staff-name {
            font-size: 0.92rem;
            min-height: 2.1rem;
        }
        .staff-meta {
            font-size: 0.67rem !important;
            line-height: 1.12;
            min-height: 1.65rem;
        }
        .staff-status {
            font-size: 0.68rem !important;
        }
        .staff-stats {
            gap: 0.35rem;
        }
        .staff-stat-item {
            min-width: 0;
            min-height: 3.2rem;
            padding: 0.45rem 0.48rem;
            border-radius: 0.75rem;
        }
        .staff-stat-item span {
            font-size: 0.58rem;
        }
        .staff-stat-item strong {
            font-size: 0.84rem;
        }
        .staff-divider {
            margin: 0.62rem 0 0.55rem;
        }
        .staff-queue-title {
            margin-bottom: 0.45rem !important;
            font-size: 0.72rem !important;
        }
        .staff-queue-item,
        .staff-queue-empty {
            min-height: 4rem;
            padding: 0.42rem 0.48rem;
            border-radius: 0.72rem;
            font-size: 0.67rem !important;
            line-height: 1.22;
        }
        .staff-load-cell {
            min-width: 86px;
        }
        .staff-load-text {
            font-size: 0.66rem;
        }
        .staff-toggle-track {
            width: 2.2rem;
            height: 1.28rem;
        }
        .staff-toggle-thumb {
            top: 0.12rem;
            left: 0.14rem;
            width: 1rem;
            height: 1rem;
        }
        .staff-toggle input:checked + .staff-toggle-track .staff-toggle-thumb {
            transform: translateX(0.9rem);
        }
    }
</style>
@endpush
