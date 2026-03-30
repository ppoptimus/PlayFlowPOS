@extends('layouts.main')

@section('title', 'พอร์ทัลร้าน - PlayFlowPOS')
@section('page_title', 'พอร์ทัลร้าน')
@section('page_subtitle', 'จัดการร้าน วันหมดอายุ และบัญชีเจ้าของร้าน')

@push('head')
<style>
    .shops-page .hero-card,
    .shops-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 16px 30px rgba(17, 81, 146, 0.09) !important;
    }

    .shops-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(14, 72, 133, 0.07);
        padding: 1rem;
        height: 100%;
    }

    .shops-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #fff !important;
    }

    .shops-page .summary-strip,
    .shops-page .metric-row {
        display: grid;
        gap: 0.75rem;
    }

    .shops-page .summary-strip {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .shops-page .metric-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .shops-page .summary-box,
    .shops-page .metric-box {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 1rem;
        background: linear-gradient(180deg, #fff, #eef8ff);
        padding: 0.95rem 1rem;
    }

    .shops-page .summary-label,
    .shops-page .metric-label {
        color: #6a85a1;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .shops-page .summary-value,
    .shops-page .metric-value {
        color: #1b4f89;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin-top: 0.2rem;
    }

    .shops-page .shop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .shops-page .shop-card {
        border: 1px solid rgba(31, 115, 224, 0.14);
        border-radius: 1.2rem;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(245,251,255,.98));
        box-shadow: 0 14px 24px rgba(17, 81, 146, 0.08);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .shops-page .shop-card.is-active-shop {
        border-color: rgba(20, 184, 154, 0.55);
        box-shadow: 0 18px 32px rgba(20, 184, 154, 0.16);
    }

    .shops-page .shop-card.is-add-card {
        border-style: dashed;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 280px;
    }

    .shops-page .shop-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(45, 143, 240, 0.15), rgba(20, 184, 154, 0.15));
        color: #1f73e0;
        font-size: 1.25rem;
    }

    .shops-page .shop-code {
        font-size: 0.8rem;
        font-weight: 700;
        color: #7090ae;
    }

    .shops-page .shop-title {
        font-size: 1.18rem;
        font-weight: 700;
        color: #21405f;
        line-height: 1.3;
    }

    .shops-page .shop-meta {
        color: #5f7893;
        font-size: 0.9rem;
        line-height: 1.35;
    }

    .shops-page .badge-row,
    .shops-page .shop-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .shops-page .card-tools {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .shops-page .card-tools form,
    .shops-page .shop-actions form {
        margin: 0;
    }

    .shops-page .shop-status-switch {
        display: inline-flex;
        align-items: center;
    }

    .shops-page .shop-status-switch__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .shops-page .shop-status-switch__label {
        position: relative;
        display: inline-flex;
        align-items: center;
        width: 5.5rem;
        height: 2.3rem;
        padding: 0.25rem;
        border-radius: 999px;
        background: linear-gradient(180deg, #ffe4e1, #ffd5cf);
        border: 1px solid rgba(220, 95, 86, 0.18);
        box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.65);
        cursor: pointer;
        transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .shops-page .shop-status-switch__label::before {
        content: '';
        position: absolute;
        top: 0.22rem;
        left: 0.22rem;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 6px 16px rgba(48, 72, 104, 0.18);
        transition: transform .22s ease;
    }

    .shops-page .shop-status-switch__text {
        position: relative;
        z-index: 1;
        width: 100%;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 800;
        color: #cf4b4b;
        letter-spacing: 0.01em;
        transition: color .2s ease;
        user-select: none;
    }

    .shops-page .shop-status-switch__input:checked + .shop-status-switch__label {
        background: linear-gradient(135deg, rgba(45, 143, 240, 0.16), rgba(20, 184, 154, 0.22));
        border-color: rgba(20, 184, 154, 0.24);
    }

    .shops-page .shop-status-switch__input:checked + .shop-status-switch__label::before {
        transform: translateX(3.1rem);
    }

    .shops-page .shop-status-switch__input:checked + .shop-status-switch__label .shop-status-switch__text {
        color: #12977f;
    }

    .shops-page .shop-status-switch__input:focus-visible + .shop-status-switch__label {
        outline: 0;
        box-shadow: 0 0 0 0.22rem rgba(45, 143, 240, 0.18);
    }

    .shops-page .shop-action-btn {
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .shops-page .shop-action-btn.is-danger {
        border-color: rgba(219, 74, 57, 0.24);
        color: #c9453f;
        background: rgba(219, 74, 57, 0.08);
    }

    .shops-page .shop-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.35rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .shops-page .shop-badge.status-ready {
        background: rgba(20, 184, 154, 0.12);
        color: #12977f;
    }

    .shops-page .shop-badge.status-off {
        background: rgba(244, 99, 99, 0.12);
        color: #cf4b4b;
    }

    .shops-page .shop-badge.status-expired {
        background: rgba(255, 193, 7, 0.2);
        color: #a87300;
    }

    .shops-page .shop-badge.expiry-badge {
        background: rgba(45, 143, 240, 0.1);
        color: #176ec7;
    }

    .shops-page .section-card {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 1rem;
        background: linear-gradient(180deg, #fbfdff, #f4faff);
        padding: 1rem;
    }

    .shops-page .section-card__title {
        font-weight: 700;
        color: #1f4f81;
        margin-bottom: 0.3rem;
    }

    .shops-page .section-card__desc {
        color: #6b7f93;
        font-size: 0.84rem;
    }

    .shops-page .lifetime-toggle {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .shops-page .lifetime-option {
        border: 1px solid rgba(31, 115, 224, 0.14);
        border-radius: 1rem;
        background: #f8fbff;
        padding: 0.85rem 0.95rem;
        cursor: pointer;
    }

    .shops-page .lifetime-option.is-selected {
        border-color: rgba(20, 184, 154, 0.45);
        background: linear-gradient(180deg, rgba(240, 255, 250, 0.95), rgba(231, 253, 247, 0.95));
        box-shadow: 0 8px 20px rgba(20, 184, 154, 0.12);
    }

    .shops-page .owner-highlight {
        border: 1px solid rgba(236, 179, 44, 0.3);
        background: linear-gradient(180deg, rgba(255, 249, 229, 0.95), rgba(255, 244, 212, 0.95));
    }

    @media (max-width: 767.98px) {
        .shops-page .summary-strip,
        .shops-page .metric-row,
        .shops-page .lifetime-toggle {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $shopCount = count($shops ?? []);
    $branchTotal = collect($shops ?? [])->sum('branch_count');
    $userTotal = collect($shops ?? [])->sum('user_count');
@endphp

<div class="shops-page row g-3">
    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-0">{{ session('success') }}</div>
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
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-0 d-flex flex-column gap-2">
                <div class="fw-bold">ระบบพอร์ทัลร้านยังไม่พร้อมใช้งาน</div>
                <div>กรุณารัน SQL setup ก่อน เพื่อสร้างตารางร้านและผูกสาขาเข้ากับร้าน</div>
                <div class="small text-muted">{{ $sqlScriptPath ?? '' }}</div>
            </div>
        </div>
    @else
        <div class="col-12">
            <div class="card hero-card border-0">
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-7">
                            <div class="soft-box">
                                <div class="fw-bold text-primary mb-3">ภาพรวมร้านในระบบ</div>
                                <div class="summary-strip">
                                    <div class="summary-box">
                                        <div class="summary-label">จำนวนร้าน</div>
                                        <div class="summary-value">{{ number_format($shopCount) }}</div>
                                    </div>
                                    <div class="summary-box">
                                        <div class="summary-label">จำนวนสาขา</div>
                                        <div class="summary-value">{{ number_format($branchTotal) }}</div>
                                    </div>
                                    <div class="summary-box">
                                        <div class="summary-label">จำนวนผู้ใช้</div>
                                        <div class="summary-value">{{ number_format($userTotal) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-5">
                            <div class="soft-box">
                                <form method="GET" action="{{ route('system.shops.index') }}" class="row g-2 align-items-end">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">ค้นหาร้าน</label>
                                        <input type="text"
                                               name="search"
                                               class="form-control"
                                               value="{{ $search }}"
                                               placeholder="ชื่อร้าน, รหัสร้าน, ผู้ติดต่อ, เจ้าของร้าน">
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn gradient-btn rounded-pill flex-grow-1">
                                            <i class="bi bi-search me-1"></i>ค้นหา
                                        </button>
                                        <button type="button" class="btn btn-outline-primary rounded-pill" onclick="pfOpenModal('addShopModal')">
                                            <i class="bi bi-plus-lg me-1"></i>เพิ่มร้าน
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="shop-grid">
                @foreach($shops as $shop)
                    @php
                        $statusClass = !$shop['is_active']
                            ? 'status-off'
                            : ($shop['is_expired'] ? 'status-expired' : 'status-ready');
                    @endphp
                    <div class="shop-card {{ (int) $activeShopId === (int) $shop['id'] ? 'is-active-shop' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="d-flex gap-3">
                                <span class="shop-icon"><i class="bi bi-shop-window"></i></span>
                                <div>
                                    <div class="shop-code">{{ $shop['code'] !== '' ? strtoupper($shop['code']) : 'SHOP-' . $shop['id'] }}</div>
                                    <div class="shop-title">{{ $shop['name'] }}</div>
                                    <div class="shop-meta">
                                        {{ $shop['contact_name'] !== '' ? $shop['contact_name'] : 'ยังไม่ได้ระบุผู้ติดต่อ' }}
                                        @if($shop['contact_phone'] !== '')
                                            • {{ $shop['contact_phone'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools">
                                <form method="POST" action="{{ route('system.shops.toggle', ['shopId' => $shop['id']]) }}" class="shop-status-switch">
                                    @csrf
                                    @method('PATCH')
                                    <input type="checkbox"
                                           class="shop-status-switch__input"
                                           id="shop-toggle-{{ $shop['id'] }}"
                                           {{ $shop['is_active'] ? 'checked' : '' }}
                                           data-auto-submit>
                                    <label class="shop-status-switch__label" for="shop-toggle-{{ $shop['id'] }}">
                                        <span class="shop-status-switch__text">{{ $shop['is_active'] ? 'เปิด' : 'ปิด' }}</span>
                                    </label>
                                </form>
                            @if((int) $activeShopId === (int) $shop['id'])
                                <span class="shop-badge status-ready"><i class="bi bi-check-circle-fill"></i>ร้านที่กำลังจัดการ</span>
                            @endif
                            </div>
                        </div>

                        <div class="badge-row">
                            <span class="shop-badge {{ $statusClass }}">{{ $shop['status_label'] }}</span>
                            <span class="shop-badge expiry-badge"><i class="bi bi-calendar-event"></i>{{ $shop['expires_label'] }}</span>
                        </div>

                        <div class="metric-row">
                            <div class="metric-box">
                                <div class="metric-label">จำนวนสาขา</div>
                                <div class="metric-value">{{ number_format($shop['branch_count']) }}</div>
                            </div>
                            <div class="metric-box">
                                <div class="metric-label">จำนวนผู้ใช้</div>
                                <div class="metric-value">{{ number_format($shop['user_count']) }}</div>
                            </div>
                        </div>

                        <div class="shop-meta">
                            <div><strong>เจ้าของร้าน:</strong> {{ $shop['owner_username'] !== '' ? $shop['owner_username'] : '-' }}</div>
                            @if($shop['notes'] !== '')
                                <div class="mt-1">{{ $shop['notes'] }}</div>
                            @endif
                        </div>

                        <div class="shop-actions mt-auto">
                            <a href="{{ route('system.shops.enter', ['shopId' => $shop['id']]) }}" class="btn gradient-btn rounded-pill px-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ร้านนี้
                            </a>
                            <button type="button"
                                    class="btn btn-outline-primary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editShopModal-{{ $shop['id'] }}">
                                <i class="bi bi-pencil-square me-1"></i>แก้ไขร้าน
                            </button>
                            <form method="POST"
                                  action="{{ route('system.shops.destroy', ['shopId' => $shop['id']]) }}"
                                  onsubmit="return confirm('ยืนยันลบร้านนี้หรือไม่? ระบบจะลบสาขา ผู้ใช้ พนักงาน และข้อมูลที่เกี่ยวข้องทั้งหมดถาวร');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger shop-action-btn is-danger">
                                    <i class="bi bi-trash3 me-1"></i>ลบร้าน
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade pf-modal" id="editShopModal-{{ $shop['id'] }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-shop-window me-2"></i>แก้ไขร้าน</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('system.shops.update', ['shopId' => $shop['id']]) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="section-card owner-highlight">
                                                    <div class="section-card__title">บัญชีเจ้าของร้าน</div>
                                                    <div class="section-card__desc">บัญชีนี้ถูกสร้างแยกจากพนักงาน และใช้บริหารร้านระดับร้าน</div>
                                                    <div class="mt-2 fw-bold">{{ $shop['owner_username'] !== '' ? $shop['owner_username'] : '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-bold">ชื่อร้าน <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $shop['name'] }}" required>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-bold">รหัสร้าน</label>
                                                <input type="text" name="code" class="form-control" value="{{ $shop['code'] }}" placeholder="ปล่อยว่างให้ระบบสร้างให้">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-bold">ผู้ติดต่อ</label>
                                                <input type="text" name="contact_name" class="form-control" value="{{ $shop['contact_name'] }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-bold">เบอร์โทร</label>
                                                <input type="text" name="contact_phone" class="form-control" value="{{ $shop['contact_phone'] }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold d-block">อายุการใช้งาน</label>
                                                <div class="lifetime-toggle" data-expiry-group>
                                                    <label class="lifetime-option {{ $shop['is_lifetime'] ? 'is-selected' : '' }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="expiry_mode" value="lifetime" {{ $shop['is_lifetime'] ? 'checked' : '' }}>
                                                            <span class="fw-bold">Life Time</span>
                                                        </div>
                                                        <div class="small text-muted mt-1">ใช้งานได้ต่อเนื่องโดยไม่กำหนดวันหมดอายุ</div>
                                                    </label>
                                                    <label class="lifetime-option {{ !$shop['is_lifetime'] ? 'is-selected' : '' }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="expiry_mode" value="date" {{ !$shop['is_lifetime'] ? 'checked' : '' }}>
                                                            <span class="fw-bold">กำหนดวันหมดอายุ</span>
                                                        </div>
                                                        <div class="small text-muted mt-1">ร้านใช้งานได้ถึงเวลา 23:59 ของวันที่เลือก</div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-bold">วันหมดอายุ</label>
                                                <input type="date"
                                                       name="expires_on"
                                                       class="form-control expiry-date-input"
                                                       value="{{ $shop['expires_on'] }}"
                                                       {{ $shop['is_lifetime'] ? 'disabled' : '' }}>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-end">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $shop['is_active'] ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold">เปิดใช้งานร้าน</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">รายละเอียดเพิ่มเติม</label>
                                                <textarea name="notes" class="form-control" rows="3">{{ $shop['notes'] }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn gradient-btn rounded-pill px-4">บันทึกการแก้ไข</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="shop-card is-add-card">
                    <span class="shop-icon" style="width: 4.25rem; height: 4.25rem; font-size: 1.6rem;">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                    <div class="shop-title">เพิ่มร้านใหม่</div>
                    <div class="shop-meta">สร้างร้านและบัญชีเจ้าของร้านใน modal เดียว ไม่ต้องไปสร้างผู้ใช้งานซ้ำอีกรอบ</div>
                    <button type="button" class="btn gradient-btn rounded-pill px-4" onclick="pfOpenModal('addShopModal')">
                        <i class="bi bi-plus-lg me-1"></i>เพิ่มร้าน
                    </button>
                </div>
            </div>
        </div>

        <div class="modal fade pf-modal" id="addShopModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-shop-window me-2"></i>เพิ่มร้านใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('system.shops.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-card">
                                        <div class="section-card__title">ข้อมูลร้าน</div>
                                        <div class="section-card__desc">กรอกข้อมูลร้านก่อน แล้วระบบจะสร้างบัญชีเจ้าของร้านให้ในขั้นตอนเดียวกัน</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">ชื่อร้าน <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">รหัสร้าน</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="ปล่อยว่างให้ระบบสร้างให้">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">ผู้ติดต่อ</label>
                                    <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">เบอร์โทร</label>
                                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                                </div>
                                <div class="col-12">
                                    <div class="section-card owner-highlight">
                                        <div class="section-card__title">บัญชีเจ้าของร้าน</div>
                                        <div class="section-card__desc">เจ้าของร้านไม่จำเป็นต้องเป็นพนักงาน ระบบจะสร้าง user แยกให้ทันที</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Username เจ้าของร้าน <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_username" class="form-control" value="{{ old('owner_username') }}" placeholder="เช่น owner_shop_a" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">รหัสผ่านเจ้าของร้าน <span class="text-danger">*</span></label>
                                    <input type="password" name="owner_password" class="form-control" placeholder="อย่างน้อย 4 ตัวอักษร" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold d-block">อายุการใช้งาน</label>
                                    <div class="lifetime-toggle" data-expiry-group>
                                        <label class="lifetime-option is-selected">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="expiry_mode" value="lifetime" checked>
                                                <span class="fw-bold">Life Time</span>
                                            </div>
                                            <div class="small text-muted mt-1">ใช้งานได้ต่อเนื่องโดยไม่กำหนดวันหมดอายุ</div>
                                        </label>
                                        <label class="lifetime-option">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="expiry_mode" value="date">
                                                <span class="fw-bold">กำหนดวันหมดอายุ</span>
                                            </div>
                                            <div class="small text-muted mt-1">ร้านใช้งานได้ถึงเวลา 23:59 ของวันที่เลือก</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">วันหมดอายุ</label>
                                    <input type="date" name="expires_on" class="form-control expiry-date-input" disabled>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <label class="form-check-label fw-bold">เปิดใช้งานร้าน</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">รายละเอียดเพิ่มเติม</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn gradient-btn rounded-pill px-4">เพิ่มร้านพร้อมเจ้าของร้าน</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function pfOpenModal(modalId) {
        var modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function setupExpiryGroup(group) {
        if (!group) return;

        var options = group.querySelectorAll('.lifetime-option');
        var radios = group.querySelectorAll('input[type="radio"][name="expiry_mode"]');
        var form = group.closest('form');
        var dateInput = form ? form.querySelector('.expiry-date-input') : null;

        function syncState() {
            var selected = group.querySelector('input[type="radio"][name="expiry_mode"]:checked');
            var mode = selected ? selected.value : 'lifetime';

            options.forEach(function (option) {
                var optionRadio = option.querySelector('input[type="radio"]');
                option.classList.toggle('is-selected', optionRadio && optionRadio.checked);
            });

            if (!dateInput) return;

            if (mode === 'date') {
                dateInput.disabled = false;
                return;
            }

            dateInput.value = '';
            dateInput.disabled = true;
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', syncState);
        });

        syncState();
    }

    document.querySelectorAll('[data-expiry-group]').forEach(setupExpiryGroup);

    document.querySelectorAll('[data-auto-submit]').forEach(function (input) {
        input.addEventListener('change', function () {
            var form = input.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
</script>
@endpush
