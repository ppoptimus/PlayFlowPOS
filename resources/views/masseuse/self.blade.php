@extends('layouts.main')

@section('title', 'ค่ามือของฉัน | PlayFlow Spa POS')
@section('page_title', 'ค่ามือของฉัน')
@section('page_subtitle', 'Masseuse Dashboard')

@push('head')
<style>
    .self-masseuse-page .self-card,
    .self-masseuse-page .self-stats {
        border-radius: 1.6rem;
        border: 1px solid rgba(45, 143, 240, 0.12);
        background: rgba(255,255,255,0.96);
        box-shadow: 0 22px 40px rgba(37, 64, 92, 0.12);
    }

    .self-masseuse-page .self-card {
        padding: 1.35rem;
    }

    .self-masseuse-page .self-head {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .self-masseuse-page .self-avatar {
        width: 88px;
        height: 88px;
        border-radius: 1.4rem;
        object-fit: cover;
        border: 3px solid rgba(45, 143, 240, 0.14);
        background: #eef8fb;
        flex-shrink: 0;
    }

    .self-masseuse-page .self-name {
        font-size: 1.55rem;
        font-weight: 800;
        color: #25405c;
        line-height: 1.1;
        margin-bottom: 0.25rem;
    }

    .self-masseuse-page .self-meta {
        color: #66819d;
        font-weight: 600;
    }

    .self-masseuse-page .self-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.46rem 0.9rem;
        border-radius: 999px;
        background: rgba(45, 143, 240, 0.1);
        color: #1e6fd2;
        font-weight: 700;
        margin-top: 0.85rem;
    }

    .self-masseuse-page .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .self-masseuse-page .self-stats {
        padding: 1rem;
    }

    .self-masseuse-page .stats-title {
        margin-bottom: 0.55rem;
        color: #6b87a2;
        font-size: 0.84rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .self-masseuse-page .stats-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #25405c;
        line-height: 1.1;
    }

    .self-masseuse-page .stats-subvalue {
        margin-top: 0.28rem;
        font-size: 0.84rem;
        color: #68839d;
        font-weight: 600;
    }

    .self-masseuse-page .empty-link {
        font-weight: 700;
        color: #5f7b96;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .self-masseuse-page .self-head {
            align-items: flex-start;
        }

        .self-masseuse-page .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $profileData = $profile ?? [];
    $recordData = $record ?? null;
@endphp

<div class="self-masseuse-page row g-3">
    <div class="col-12">
        <section class="self-card">
            <div class="self-head">
                <img src="{{ $recordData['avatar'] ?? ($profileData['avatar'] ?? '') }}" alt="{{ $recordData['name'] ?? ($profileData['display_name'] ?? 'User') }}" class="self-avatar">
                <div class="min-w-0">
                    <div class="self-name">{{ $recordData['name'] ?? ($profileData['display_name'] ?? 'ผู้ใช้งาน') }}</div>
                    <div class="self-meta">
                        {{ $recordData['display_id'] ?? ($profileData['username'] ?? '-') }}
                        @if(!empty($profileData['branch_name']) && $profileData['branch_name'] !== '-')
                        <span class="mx-1">|</span>{{ $profileData['branch_name'] }}
                        @endif
                    </div>
                    <div class="self-pill">
                        <i class="bi bi-wallet2"></i>
                        <span>ข้อมูลของฉันเท่านั้น</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if($isLinked && is_array($recordData))
    <div class="col-12">
        <div class="stats-grid">
            <section class="self-stats">
                <div class="stats-title">วันนี้</div>
                <div class="stats-value">{{ number_format((int) ($recordData['income'] ?? 0)) }} ฿</div>
                <div class="stats-subvalue">ค่ามือ {{ number_format((int) ($recordData['commission'] ?? 0)) }} ฿ | {{ number_format((int) ($recordData['daily_queue_count'] ?? 0)) }} คิว</div>
            </section>
            <section class="self-stats">
                <div class="stats-title">เมื่อวาน</div>
                <div class="stats-value">{{ number_format((int) ($recordData['yesterday_income'] ?? 0)) }} ฿</div>
                <div class="stats-subvalue">ค่ามือ {{ number_format((int) ($recordData['yesterday_commission'] ?? 0)) }} ฿ | {{ number_format((int) ($recordData['yesterday_queue_count'] ?? 0)) }} คิว</div>
            </section>
            <section class="self-stats">
                <div class="stats-title">เดือนนี้</div>
                <div class="stats-value">{{ number_format((int) ($recordData['monthly_income'] ?? 0)) }} ฿</div>
                <div class="stats-subvalue">ค่ามือ {{ number_format((int) ($recordData['monthly_commission'] ?? 0)) }} ฿ | {{ number_format((int) ($recordData['monthly_queue_count'] ?? 0)) }} คิว</div>
            </section>
        </div>
    </div>
    @else
    <div class="col-12">
        <section class="self-card empty-link">
            บัญชีนี้ยังไม่ได้ผูกกับข้อมูลหมอนวดในระบบ จึงยังไม่สามารถแสดงค่ามือส่วนตัวได้
        </section>
    </div>
    @endif
</div>
@endsection
