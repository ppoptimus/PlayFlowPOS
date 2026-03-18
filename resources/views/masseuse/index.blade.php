@extends('layouts.main')

@section('title', 'Masseuse | PlayFlow Spa POS')
@section('page_title', 'Masseuse Management')
@section('page_subtitle', 'จัดการข้อมูลหมอนวด โปรไฟล์ และสรุปรายได้')

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
@endphp

@push('head')
<style>
    .masseuse-page .hero-card,
    .masseuse-page .module-card,
    .masseuse-page .staff-card,
    .masseuse-page .editor-card,
    .masseuse-page .attendance-card {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 1.3rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 250, 255, 0.96));
        box-shadow: 0 16px 32px rgba(17, 81, 146, 0.08);
    }

    .masseuse-page .hero-card {
        background: linear-gradient(140deg, rgba(34, 112, 193, 0.98), rgba(20, 184, 154, 0.95));
        color: #ffffff;
        overflow: hidden;
        position: relative;
    }

    .masseuse-page .hero-card::after {
        content: '';
        position: absolute;
        inset: auto -8% -45% auto;
        width: 14rem;
        height: 14rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .masseuse-page .hero-title {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .masseuse-page .hero-subtitle {
        max-width: 42rem;
        color: rgba(255, 255, 255, 0.82);
    }

    .masseuse-page .hero-metric {
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.16);
        padding: 0.85rem 0.95rem;
        min-height: 100%;
    }

    .masseuse-page .hero-metric-label {
        display: block;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 0.2rem;
    }

    .masseuse-page .hero-metric-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ffffff;
    }

    .masseuse-page .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #234262;
        margin-bottom: 0;
    }

    .masseuse-page .section-subtitle {
        font-size: 0.84rem;
        color: #68809a;
    }

    .masseuse-page .staff-card {
        padding: 1rem;
        height: 100%;
    }

    .masseuse-page .staff-card.is-off-duty {
        background: linear-gradient(180deg, rgba(243, 246, 249, 0.98), rgba(235, 240, 245, 0.96));
        border-color: rgba(141, 153, 168, 0.2);
    }

    .masseuse-page .staff-head,
    .masseuse-page .editor-head {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .masseuse-page .staff-avatar,
    .masseuse-page .editor-avatar {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(45, 143, 240, 0.26);
        background: #ffffff;
        flex-shrink: 0;
    }

    .masseuse-page .staff-name,
    .masseuse-page .editor-name {
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.1;
        color: #234262;
        margin-bottom: 0.12rem;
    }

    .masseuse-page .staff-id,
    .masseuse-page .editor-id {
        font-size: 0.77rem;
        color: #6f8498;
    }

    .masseuse-page .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.32rem 0.68rem;
        font-size: 0.78rem;
        font-weight: 700;
        background: rgba(31, 115, 224, 0.1);
        color: #1d67bd;
    }

    .masseuse-page .status-pill.is-success {
        background: rgba(20, 184, 154, 0.12);
        color: #108974;
    }

    .masseuse-page .status-pill.is-warning {
        background: rgba(242, 179, 64, 0.16);
        color: #a56a00;
    }

    .masseuse-page .status-pill.is-muted {
        background: rgba(109, 124, 142, 0.12);
        color: #5f7182;
    }

    .masseuse-page .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 0.95rem;
    }

    .masseuse-page .stat-box {
        padding: 0.72rem 0.75rem;
        border-radius: 0.95rem;
        background: rgba(239, 247, 255, 0.92);
        border: 1px solid rgba(31, 115, 224, 0.08);
    }

    .masseuse-page .stat-label {
        display: block;
        font-size: 0.72rem;
        color: #6d8398;
        margin-bottom: 0.12rem;
    }

    .masseuse-page .stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: #234262;
    }

    .masseuse-page .queue-box {
        margin-top: 0.95rem;
        border-radius: 0.95rem;
        border: 1px solid rgba(31, 115, 224, 0.08);
        background: rgba(250, 252, 255, 0.95);
        padding: 0.78rem 0.82rem;
        min-height: 5.35rem;
    }

    .masseuse-page .queue-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: #234262;
        margin-bottom: 0.45rem;
    }

    .masseuse-page .queue-meta {
        display: block;
        line-height: 1.3;
        color: #5d738a;
        font-size: 0.8rem;
    }

    .masseuse-page .queue-time {
        color: #1d67bd;
        font-weight: 700;
    }

    .masseuse-page .editor-card,
    .masseuse-page .module-card,
    .masseuse-page .attendance-card {
        padding: 1rem;
    }

    .masseuse-page .module-card {
        position: sticky;
        top: 1rem;
    }

    .masseuse-page .editor-card + .editor-card {
        margin-top: 0.9rem;
    }

    .masseuse-page .editor-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.9rem;
    }

    .masseuse-page .soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.34rem 0.65rem;
        background: rgba(31, 115, 224, 0.08);
        color: #275f9c;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .masseuse-page .empty-state {
        text-align: center;
        color: #70859a;
        padding: 2.2rem 1rem;
        border-radius: 1rem;
        border: 1px dashed rgba(31, 115, 224, 0.18);
        background: rgba(255, 255, 255, 0.78);
    }

    .masseuse-page .attendance-table th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
    }

    .masseuse-page .attendance-table td,
    .masseuse-page .attendance-table th {
        vertical-align: middle;
    }

    .masseuse-page .attendance-col {
        width: 1%;
        white-space: nowrap;
        text-align: center;
    }

    .masseuse-page .attendance-row-off {
        color: #8d99a8;
    }

    .masseuse-page .attendance-row-off td {
        background-color: rgba(239, 243, 246, 0.65) !important;
    }

    .masseuse-page .toggle {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .masseuse-page .toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .masseuse-page .toggle-track {
        width: 2.8rem;
        height: 1.6rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #c4d3e2, #d7e1ea);
        position: relative;
        box-shadow: inset 0 1px 3px rgba(29, 61, 94, 0.16);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .masseuse-page .toggle-thumb {
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

    .masseuse-page .toggle input:checked + .toggle-track {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        box-shadow: inset 0 1px 3px rgba(17, 88, 118, 0.22);
    }

    .masseuse-page .toggle input:checked + .toggle-track .toggle-thumb {
        transform: translateX(1.18rem);
    }

    .masseuse-page .action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        justify-content: space-between;
        align-items: center;
    }

    .masseuse-page .queue-load {
        min-width: 110px;
    }

    .masseuse-page .helper-text {
        font-size: 0.76rem;
        color: #6e8193;
    }

    .masseuse-page .upload-note {
        margin-top: 0.45rem;
        font-size: 0.76rem;
        color: #5f7488;
        line-height: 1.35;
    }

    .masseuse-page .upload-note.is-error {
        color: #c54b57;
    }

    .masseuse-page .upload-picker {
        border: 1px dashed rgba(31, 115, 224, 0.24);
        border-radius: 1.15rem;
        background: linear-gradient(180deg, rgba(246, 251, 255, 0.98), rgba(238, 247, 255, 0.94));
        padding: 0.8rem;
    }

    .masseuse-page .upload-stage {
        width: 100%;
        margin: 0;
        cursor: pointer;
    }

    .masseuse-page .upload-frame {
        min-height: 190px;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(31, 115, 224, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .masseuse-page .upload-stage:hover .upload-frame {
        border-color: rgba(20, 184, 154, 0.34);
        box-shadow: 0 10px 24px rgba(17, 81, 146, 0.08);
        transform: translateY(-1px);
    }

    .masseuse-page .upload-picker.has-image .upload-placeholder {
        display: none;
    }

    .masseuse-page .upload-picker:not(.has-image) .upload-preview-image {
        display: none;
    }

    .masseuse-page .upload-preview-image {
        width: 100%;
        max-width: 180px;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 1rem;
        background: #ffffff;
    }

    .masseuse-page .upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        text-align: center;
        padding: 1rem;
        color: #54708b;
    }

    .masseuse-page .upload-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(45, 143, 240, 0.14), rgba(20, 184, 154, 0.16));
        color: #1f73e0;
        font-size: 1.7rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }

    .masseuse-page .upload-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #234262;
    }

    .masseuse-page .upload-subtitle {
        font-size: 0.78rem;
        line-height: 1.35;
        color: #667f96;
        max-width: 16rem;
    }

    @media (max-width: 991.98px) {
        .masseuse-page .module-card {
            position: static;
        }

        .masseuse-page .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .masseuse-page .hero-title {
            font-size: 1.4rem;
        }

        .masseuse-page .stats-grid {
            grid-template-columns: 1fr;
        }

        .masseuse-page .staff-head,
        .masseuse-page .editor-head {
            align-items: flex-start;
        }

        .masseuse-page .staff-avatar,
        .masseuse-page .editor-avatar {
            width: 50px;
            height: 50px;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 masseuse-page">
    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-0">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
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
                        สรุปรายได้ คอมมิชชั่น และคิวงาน พร้อมฟอร์มจัดการข้อมูลส่วนตัวและรูปโปรไฟล์ในหน้าเดียว
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
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-1">
            <div>
                <h3 class="section-title">สรุปผลงานรายคน</h3>
                <div class="section-subtitle">ดึงจากคิวในวันที่ {{ $selectedDate }}</div>
            </div>
            <div class="helper-text">รายได้และคอมมิชชั่นคำนวณจากคิวและ commission config ที่ผูกกับบริการ</div>
        </div>
    </div>

    @forelse($staff as $s)
    @php
        [$firstName, $lastName] = $splitDisplayName((string) $s['name']);
        $latestQueue = $s['queue'][0] ?? null;
        $statusClass = !empty($s['isWorkingToday'])
            ? (!empty($s['queue']) ? 'is-success' : 'is-warning')
            : 'is-muted';
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <section class="staff-card{{ $s['isWorkingToday'] ? '' : ' is-off-duty' }}">
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
            <div>เริ่มต้นโดยเพิ่มข้อมูลหมอนวดในส่วนจัดการด้านล่าง</div>
        </div>
    </div>
    @endforelse

    @if($canManage)
    <div class="col-12 pt-2">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-1">
            <div>
                <h3 class="section-title">จัดการข้อมูลส่วนตัวและรูปโปรไฟล์</h3>
                <div class="section-subtitle">เพิ่ม แก้ไข หรือลบหมอนวดได้จากหน้านี้</div>
            </div>
            <div class="helper-text">รองรับรูปภาพสูงสุด 2MB ต่อไฟล์</div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <section class="module-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="section-title">เพิ่มหมอนวดใหม่</h4>
                    <div class="section-subtitle">ระบุข้อมูลพื้นฐานและรูปโปรไฟล์</div>
                </div>
                <span class="soft-badge"><i class="bi bi-person-plus-fill"></i> New</span>
            </div>

            <form method="POST" action="{{ route('masseuse.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $activeBranchId }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="col-12">
                    <label class="form-label small fw-bold">ชื่อเล่น</label>
                    <input type="text" name="nickname" class="form-control" value="{{ old('nickname') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">ชื่อเต็ม</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">สถานะหลัก</label>
                    <select name="status" class="form-select" required>
                        @foreach($statusOptions as $option)
                        <option value="{{ $option['value'] }}" {{ old('status', 'available') === $option['value'] ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">รูปโปรไฟล์</label>
                    <div class="upload-picker" data-upload-picker="create">
                        <label class="upload-stage" for="create_profile_image">
                            <div class="upload-frame">
                                <img
                                    src=""
                                    alt="ตัวอย่างรูปโปรไฟล์ใหม่"
                                    class="upload-preview-image"
                                    data-image-preview="create"
                                >
                                <div class="upload-placeholder" data-image-placeholder="create">
                                    <span class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                    <div class="upload-title">อัปโหลดรูปโปรไฟล์</div>
                                    <div class="upload-subtitle">แตะเพื่อเลือกรูปที่ต้องการใช้ ระบบจะย่อขนาดไฟล์ให้อัตโนมัติก่อนอัปโหลด (ขนาดไม่เกิน 2 MB)</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <input
                        id="create_profile_image"
                        type="file"
                        name="profile_image"
                        class="form-control mt-1"
                        accept="image/*"
                        data-compress-image="true"
                        data-preview-target="create"
                    >
                  
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">ทักษะ / รายละเอียดเพิ่มเติม</label>
                    <textarea name="skills_description" rows="5" class="form-control" placeholder="เช่น นวดไทย นวดน้ำมัน กดจุด">{{ old('skills_description') }}</textarea>
                </div>
                <div class="col-12 d-grid">
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="bi bi-save2 me-1"></i> บันทึกหมอนวดใหม่
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="col-12 col-xl-8">
        @forelse($staffRecords as $record)
        <section class="editor-card">
            <div class="editor-head justify-content-between flex-wrap">
                <div class="editor-head">
                    <img src="{{ $record['avatar'] }}" alt="{{ $record['name'] }}" class="editor-avatar">
                    <div>
                        <div class="editor-name">{{ $record['name'] !== '' ? $record['name'] : $record['nickname'] }}</div>
                        <div class="editor-id">{{ $record['display_id'] }}</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="status-pill">{{ $record['status_label'] }}</span>
                    <span class="status-pill {{ $record['is_working_today'] ? 'is-success' : 'is-muted' }}">
                        {{ $record['is_working_today'] ? 'มาทำงานวันนี้' : 'ไม่มาทำงานวันนี้' }}
                    </span>
                </div>
            </div>

            <div class="editor-badges">
                <span class="soft-badge"><i class="bi bi-cash-coin"></i> {{ number_format($record['income']) }} ฿</span>
                <span class="soft-badge"><i class="bi bi-cash-stack"></i> {{ number_format($record['commission']) }} ฿</span>
                <span class="soft-badge"><i class="bi bi-calendar2-check"></i> {{ number_format($record['queue_count']) }} คิว</span>
                <span class="soft-badge"><i class="bi bi-bar-chart-line"></i> Load {{ $record['queue_load'] }}%</span>
                <span class="soft-badge"><i class="bi bi-activity"></i> {{ $record['performance_status'] }}</span>
            </div>

            <form method="POST" action="{{ route('masseuse.update', ['staffId' => $record['id']]) }}" enctype="multipart/form-data" class="row g-3 mt-1">
                @csrf
                @method('PUT')
                <input type="hidden" name="branch_id" value="{{ $activeBranchId }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">รูปปัจจุบัน</label>
                    <div class="upload-picker{{ $record['profile_image'] !== '' ? ' has-image' : '' }}" data-upload-picker="edit-{{ $record['id'] }}">
                        <label class="upload-stage" for="edit_profile_image_{{ $record['id'] }}">
                            <div class="upload-frame">
                                <img
                                    src="{{ $record['profile_image'] !== '' ? $record['avatar'] : '' }}"
                                    alt="{{ $record['name'] }}"
                                    class="upload-preview-image"
                                    data-image-preview="edit-{{ $record['id'] }}"
                                >
                                <div class="upload-placeholder" data-image-placeholder="edit-{{ $record['id'] }}">
                                    <span class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                    <div class="upload-title">{{ $record['profile_image'] !== '' ? 'เปลี่ยนรูปโปรไฟล์' : 'ยังไม่มีรูปโปรไฟล์' }}</div>
                                    <div class="upload-subtitle">{{ $record['profile_image'] !== '' ? 'แตะเพื่อเลือกรูปใหม่แทนของเดิม' : 'แตะเพื่ออัปโหลดรูปโปรไฟล์ของหมอนวดคนนี้' }}</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" name="remove_profile_image" id="remove_profile_image_{{ $record['id'] }}">
                        <label class="form-check-label small" for="remove_profile_image_{{ $record['id'] }}">ลบรูปโปรไฟล์เดิม</label>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-bold">ชื่อเล่น</label>
                            <input type="text" name="nickname" class="form-control" value="{{ $record['nickname'] }}" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-bold">ชื่อเต็ม</label>
                            <input type="text" name="full_name" class="form-control" value="{{ $record['full_name'] }}">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-bold">สถานะหลัก</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusOptions as $option)
                                <option value="{{ $option['value'] }}" {{ $record['status_value'] === $option['value'] ? 'selected' : '' }}>
                                    {{ $option['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-bold">อัปโหลดรูปใหม่</label>
                            <input
                                id="edit_profile_image_{{ $record['id'] }}"
                                type="file"
                                name="profile_image"
                                class="form-control"
                                accept="image/*"
                                data-compress-image="true"
                                data-preview-target="edit-{{ $record['id'] }}"
                            >
                            <div class="upload-note" data-upload-note="edit-{{ $record['id'] }}">
                                ระบบจะย่อรูปให้อัตโนมัติก่อนส่ง ถ้า browser รองรับ
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ทักษะ / รายละเอียดเพิ่มเติม</label>
                            <textarea name="skills_description" rows="4" class="form-control" placeholder="เช่น นวดไทย นวดน้ำมัน กดจุด">{{ $record['skills_description'] }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="action-row">
                        <div class="helper-text">
                            @if($record['latest_queue'])
                            คิวล่าสุด {{ $record['latest_queue']['start'] }} - {{ $record['latest_queue']['end'] }} | {{ $record['latest_queue']['customer'] }} | {{ $record['latest_queue']['service'] }}
                            @else
                            ยังไม่มีคิวในวันที่เลือก
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-save2 me-1"></i> อัปเดต
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('masseuse.destroy', ['staffId' => $record['id']]) }}" class="mt-3" onsubmit="return confirm('ต้องการลบข้อมูลหมอนวด {{ $record['name'] !== '' ? $record['name'] : $record['nickname'] }} ใช่หรือไม่?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="branch_id" value="{{ $activeBranchId }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash3 me-1"></i> ลบข้อมูลหมอนวด
                </button>
            </form>
        </section>
        @empty
        <div class="empty-state">
            <div class="fw-bold mb-1">ยังไม่มีรายการสำหรับแก้ไข</div>
            <div>เพิ่มหมอนวดใหม่จากฟอร์มด้านซ้ายก่อน</div>
        </div>
        @endforelse
    </div>
    @endif

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

@push('scripts')
<script>
    (function () {
        const MAX_UPLOAD_BYTES = 1600 * 1024;
        const MAX_DIMENSION = 1600;

        function formatBytes(bytes) {
            if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            return Math.round(bytes / 1024) + ' KB';
        }

        function setUploadNote(targetKey, message, isError) {
            const noteEl = document.querySelector('[data-upload-note="' + targetKey + '"]');
            if (!noteEl) return;
            noteEl.textContent = message;
            noteEl.classList.toggle('is-error', Boolean(isError));
        }

        function setPreview(targetKey, file) {
            const previewEl = document.querySelector('[data-image-preview="' + targetKey + '"]');
            const pickerEl = document.querySelector('[data-upload-picker="' + targetKey + '"]');
            if (!previewEl || !file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                previewEl.src = String((event.target && event.target.result) || previewEl.src);
                if (pickerEl) {
                    pickerEl.classList.add('has-image');
                }
            };
            reader.readAsDataURL(file);
        }

        function loadImage(file) {
            return new Promise(function (resolve, reject) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const image = new Image();
                    image.onload = function () { resolve(image); };
                    image.onerror = reject;
                    image.src = String((event.target && event.target.result) || '');
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        function canvasToJpegBlob(canvas, quality) {
            return new Promise(function (resolve, reject) {
                if (!canvas.toBlob) {
                    reject(new Error('Canvas toBlob is not supported'));
                    return;
                }

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('Unable to create compressed blob'));
                        return;
                    }

                    resolve(blob);
                }, 'image/jpeg', quality);
            });
        }

        async function compressImage(file) {
            const image = await loadImage(file);

            let width = image.naturalWidth || image.width;
            let height = image.naturalHeight || image.height;
            const maxSide = Math.max(width, height);
            const scale = maxSide > MAX_DIMENSION ? (MAX_DIMENSION / maxSide) : 1;

            width = Math.max(1, Math.round(width * scale));
            height = Math.max(1, Math.round(height * scale));

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            if (!context) {
                throw new Error('Canvas context unavailable');
            }

            canvas.width = width;
            canvas.height = height;

            // Flatten transparent backgrounds so every source format can become a light JPEG avatar.
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.drawImage(image, 0, 0, width, height);

            let quality = 0.86;
            let blob = await canvasToJpegBlob(canvas, quality);

            while (blob.size > MAX_UPLOAD_BYTES && quality > 0.46) {
                quality -= 0.08;
                blob = await canvasToJpegBlob(canvas, quality);
            }

            if (blob.size > MAX_UPLOAD_BYTES) {
                let nextWidth = width;
                let nextHeight = height;

                while (blob.size > MAX_UPLOAD_BYTES && nextWidth > 480 && nextHeight > 480) {
                    nextWidth = Math.max(480, Math.round(nextWidth * 0.86));
                    nextHeight = Math.max(480, Math.round(nextHeight * 0.86));
                    canvas.width = nextWidth;
                    canvas.height = nextHeight;
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, nextWidth, nextHeight);
                    context.drawImage(image, 0, 0, nextWidth, nextHeight);
                    blob = await canvasToJpegBlob(canvas, Math.max(quality, 0.46));
                }
            }

            return new File(
                [blob],
                (file.name || 'profile').replace(/\.[^.]+$/, '') + '.jpg',
                { type: 'image/jpeg', lastModified: Date.now() }
            );
        }

        async function handleFileInput(input) {
            const targetKey = String(input.getAttribute('data-preview-target') || '');
            const file = input.files && input.files[0] ? input.files[0] : null;

            if (!targetKey || !file) {
                return;
            }

            setUploadNote(targetKey, 'กำลังเตรียมรูปภาพ...', false);

            try {
                let finalFile = file;

                if (file.size > MAX_UPLOAD_BYTES) {
                    finalFile = await compressImage(file);

                    if (typeof DataTransfer !== 'undefined') {
                        const transfer = new DataTransfer();
                        transfer.items.add(finalFile);
                        input.files = transfer.files;
                    }
                }

                setPreview(targetKey, finalFile);
                setUploadNote(
                    targetKey,
                    file.size > finalFile.size
                        ? 'ย่อรูปแล้วจาก ' + formatBytes(file.size) + ' เหลือ ' + formatBytes(finalFile.size)
                        : 'ไฟล์พร้อมอัปโหลด ขนาด ' + formatBytes(finalFile.size),
                    finalFile.size > MAX_UPLOAD_BYTES
                );

                if (finalFile.size > MAX_UPLOAD_BYTES) {
                    setUploadNote(
                        targetKey,
                        'ไฟล์ยังใหญ่เกินไปสำหรับระบบ กรุณาเลือกรูปขนาดเล็กลงอีกเล็กน้อย',
                        true
                    );
                }
            } catch (error) {
                setPreview(targetKey, file);
                setUploadNote(
                    targetKey,
                    'browser นี้ย่อรูปอัตโนมัติไม่ได้ กรุณาใช้ไฟล์ไม่เกิน 2 MB',
                    true
                );
            }
        }

        document.querySelectorAll('input[type="file"][data-compress-image="true"]').forEach(function (input) {
            input.addEventListener('change', function () {
                handleFileInput(input);
            });
        });
    })();
</script>
@endpush
