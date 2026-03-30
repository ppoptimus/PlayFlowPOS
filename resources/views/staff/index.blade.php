@extends('layouts.main')

@section('title', 'จัดการพนักงาน - PlayFlow')
@section('page_title', 'จัดการพนักงาน')
@section('page_subtitle', 'Staff Management')

@push('head')
<style>
    .staff-page .card {
        border-radius: 1.05rem;
    }

    .staff-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .staff-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }

    .staff-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .staff-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }

    .staff-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .staff-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }

    .staff-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .staff-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .staff-page .badge-linked,
    .staff-page .badge-unlinked {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .staff-page .badge-linked {
        color: #0c907d;
        background: rgba(20, 184, 154, 0.14);
    }

    .staff-page .badge-unlinked {
        color: #7a8492;
        background: rgba(108, 117, 125, 0.12);
    }

    .staff-page .icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }

    .staff-page .icon-chip--blue {
        color: #0f67bf;
        background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16));
    }

    .staff-page .staff-summary {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .staff-page .staff-avatar {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        object-fit: cover;
        border: 2px solid rgba(31, 115, 224, 0.14);
        box-shadow: 0 8px 18px rgba(24, 82, 144, 0.12);
        background: #ffffff;
        flex-shrink: 0;
    }

    .staff-page .staff-copy {
        min-width: 0;
    }

    .staff-page .staff-name {
        font-weight: 700;
        color: #1f456c;
        line-height: 1.15;
    }

    .staff-page .staff-meta {
        color: #6b7f93;
        font-size: 0.78rem;
        line-height: 1.25;
    }

    .staff-page .status-pill {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        padding: 0.24rem 0.65rem;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .staff-page .status-pill.is-active {
        color: #0f8a74;
        background: rgba(20, 184, 154, 0.14);
    }

    .staff-page .status-pill.is-inactive {
        color: #9a4756;
        background: rgba(220, 53, 69, 0.12);
    }

    .staff-page .pf-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.1rem;
        box-shadow: 0 24px 48px rgba(14, 60, 120, 0.18);
    }

    .staff-page .pf-modal .modal-header {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        border-radius: 1.1rem 1.1rem 0 0;
        border-bottom: none;
    }

    .staff-page .pf-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .staff-page .pf-modal .modal-body {
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .staff-page .pf-modal .modal-footer {
        border-top: 1px solid rgba(31, 115, 224, 0.1);
    }

    .staff-page .upload-picker {
        position: relative;
    }

    .staff-page .upload-stage {
        display: block;
        cursor: pointer;
    }

    .staff-page .upload-frame {
        position: relative;
        border-radius: 1.2rem;
        border: 1px dashed rgba(31, 115, 224, 0.22);
        background: linear-gradient(180deg, #f6fbff 0%, #ffffff 100%);
        min-height: 220px;
        overflow: hidden;
    }

    .staff-page .upload-preview-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
    }

    .staff-page .upload-picker:not(.has-image) .upload-preview-image {
        display: none;
    }

    .staff-page .upload-placeholder {
        min-height: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.55rem;
        padding: 1rem;
        color: #2a5d92;
    }

    .staff-page .upload-picker.has-image .upload-placeholder {
        display: none;
    }

    .staff-page .upload-icon {
        width: 3.1rem;
        height: 3.1rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(45, 143, 240, 0.12);
        color: #1870cb;
        font-size: 1.25rem;
    }

    .staff-page .upload-title {
        font-weight: 700;
    }

    .staff-page .upload-subtitle,
    .staff-page .upload-note {
        color: #6b7f93;
        font-size: 0.78rem;
    }

    .staff-page .upload-note.is-error {
        color: #cc3b51;
    }

    @media (max-width: 767.98px) {
        .staff-page .card-body {
            padding: 0.9rem;
        }

        .staff-page .form-label,
        .staff-page .small {
            font-size: 0.78rem;
        }

        .staff-page .form-control,
        .staff-page .btn,
        .staff-page .form-select {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 staff-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง staff</div>
            <div>โปรดตรวจสอบฐานข้อมูลจริงของระบบก่อนใช้งานโมดูลนี้</div>
        </div>
    </div>
    @elseif(($canManageAllBranches ?? false) && !($shopSelected ?? true))
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-0 d-flex flex-column gap-2">
            <div class="fw-bold">กรุณาเลือกร้านจากพอร์ทัลก่อน</div>
            <div>เมนูพนักงานจะอ้างอิงตามร้านที่คุณเลือกอยู่ในพอร์ทัลร้าน</div>
            <div>
                <a href="{{ route('system.shops.index') }}" class="btn gradient-btn rounded-pill px-4">ไปพอร์ทัลร้าน</a>
            </div>
        </div>
    </div>
    @elseif($requiresBranchSetup ?? false)
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-0 d-flex flex-column gap-2">
            <div class="fw-bold">ร้านนี้ยังไม่มีสาขา</div>
            <div>กรุณาสร้างสาขาแรกก่อน แล้วค่อยกลับมาเพิ่มพนักงานของร้านนี้</div>
            <div>
                <a href="{{ route('branches.index') }}" class="btn gradient-btn rounded-pill px-4">ไปสร้างสาขาแรก</a>
            </div>
        </div>
    </div>
    @else

    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                            <div class="section-title mb-0">จัดการพนักงานทั่วไป</div>
                            <button type="button" class="btn gradient-btn rounded-3 w-100" onclick="pfOpenModal('addStaffModal')">
                                <i class="bi bi-plus-lg me-1"></i>เพิ่มพนักงานใหม่
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="soft-box">
                            <form method="GET" action="{{ route('staff.index') }}" class="row g-2 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-bold">ค้นหาพนักงาน</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="bi bi-search"></i></span></span>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อ, ชื่อเล่น, เบอร์โทร, ตำแหน่ง">
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-bold">สาขา</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">ทุกสาขา</option>
                                        @foreach($branches as $br)
                                        <option value="{{ $br['id'] }}" {{ ($branchFilter ?? null) == $br['id'] ? 'selected' : '' }}>{{ $br['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 d-grid">
                                    <button type="submit" class="btn gradient-btn rounded-3"><i class="bi bi-filter me-1"></i>กรอง</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--blue me-2"><i class="bi bi-person-badge-fill"></i></span>รายการพนักงาน</h6>
                <span class="badge-soft">{{ count($staffList) }} คน</span>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 52px;">#</th>
                                <th style="min-width: 260px;">พนักงาน</th>
                                <th style="min-width: 130px;">ตำแหน่ง</th>
                                <th style="min-width: 120px;">สาขา</th>
                                <th style="min-width: 130px;">บัญชีผู้ใช้</th>
                                <th style="min-width: 90px;">สถานะ</th>
                                <th class="text-end" style="min-width: 160px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffList as $idx => $staff)
                            @php
                                $modalId = 'editStaffModal' . $staff['id'];
                                $previewKey = 'staff-edit-' . $staff['id'];
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="staff-summary">
                                        <img src="{{ $staff['avatar'] }}" alt="{{ $staff['name'] }}" class="staff-avatar">
                                        <div class="staff-copy">
                                            <div class="staff-name">{{ $staff['name'] }}</div>
                                            <div class="staff-meta">
                                                @if($staff['nickname']) {{ $staff['nickname'] }} @else ไม่มีชื่อเล่น @endif
                                                @if($staff['phone']) | {{ $staff['phone'] }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $staff['position'] !== '' ? $staff['position'] : '-' }}</td>
                                <td>{{ $staff['branch_name'] }}</td>
                                <td>
                                    @if($staff['linked_user_id'])
                                    <span class="badge-linked">มีบัญชีผู้ใช้แล้ว</span>
                                    @else
                                    <span class="badge-unlinked">ยังไม่ผูกบัญชี</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $staff['is_active'] ? 'is-active' : 'is-inactive' }}">
                                        {{ $staff['is_active'] ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="pfOpenModal('{{ $modalId }}')">
                                            <i class="bi bi-pencil-square me-1"></i>แก้ไข
                                        </button>
                                        <form method="POST" action="{{ route('staff.destroy', ['staffId' => $staff['id']]) }}" onsubmit="return confirm('ยืนยันลบพนักงาน {{ addslashes($staff['name']) }} ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">ยังไม่มีข้อมูลพนักงาน</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade pf-modal" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addStaffModalLabel"><i class="bi bi-person-badge-fill me-2"></i>เพิ่มพนักงานใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('staff.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold">รูปโปรไฟล์</label>
                                <div class="upload-picker" data-upload-picker="staff-create">
                                    <label class="upload-stage" for="profile_image_staff_create">
                                        <div class="upload-frame">
                                            <img src="" alt="รูปโปรไฟล์พนักงาน" class="upload-preview-image" data-image-preview="staff-create">
                                            <div class="upload-placeholder" data-image-placeholder="staff-create">
                                                <span class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                                                <div class="upload-title">อัปโหลดรูปโปรไฟล์</div>
                                                <div class="upload-subtitle">ใช้รูปจริงสำหรับหน้าโปรไฟล์และ navbar</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <input
                                    id="profile_image_staff_create"
                                    type="file"
                                    name="profile_image"
                                    class="form-control mt-2"
                                    accept="image/*"
                                    data-compress-image="true"
                                    data-preview-target="staff-create"
                                >
                                <div class="upload-note" data-upload-note="staff-create">ระบบจะพยายามย่อรูปก่อนอัปโหลดอัตโนมัติ</div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ชื่อพนักงาน <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="ชื่อ-นามสกุล" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ชื่อเล่น</label>
                                        <input type="text" name="nickname" class="form-control" value="{{ old('nickname') }}" placeholder="ชื่อเล่น">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">เบอร์โทร</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0xx-xxx-xxxx">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ตำแหน่ง</label>
                                        <select name="position" class="form-select">
                                            <option value="">เลือกตำแหน่ง</option>
                                            <option value="แคชเชียร์" {{ old('position') === 'แคชเชียร์' ? 'selected' : '' }}>แคชเชียร์</option>
                                            <option value="ผู้จัดการ" {{ old('position') === 'ผู้จัดการ' ? 'selected' : '' }}>ผู้จัดการ</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">สาขา</label>
                                        <select name="branch_id" class="form-select">
                                            <option value="">ไม่ระบุ</option>
                                            @foreach($branches as $br)
                                            <option value="{{ $br['id'] }}" {{ old('branch_id') == $br['id'] ? 'selected' : '' }}>{{ $br['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i>เพิ่มพนักงาน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($staffList as $staff)
    @php
        $modalId = 'editStaffModal' . $staff['id'];
        $previewKey = 'staff-edit-' . $staff['id'];
    @endphp
    <div class="modal fade pf-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Label"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลพนักงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('staff.update', ['staffId' => $staff['id']]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold">รูปโปรไฟล์</label>
                                <div class="upload-picker{{ $staff['profile_image'] !== '' ? ' has-image' : '' }}" data-upload-picker="{{ $previewKey }}">
                                    <label class="upload-stage" for="profile_image_{{ $previewKey }}">
                                        <div class="upload-frame">
                                            <img src="{{ $staff['avatar'] }}" alt="{{ $staff['name'] }}" class="upload-preview-image" data-image-preview="{{ $previewKey }}">
                                            <div class="upload-placeholder" data-image-placeholder="{{ $previewKey }}">
                                                <span class="upload-icon"><i class="fa-solid fa-camera"></i></span>
                                                <div class="upload-title">เปลี่ยนรูปโปรไฟล์</div>
                                                <div class="upload-subtitle">แตะเพื่อเลือกรูปใหม่</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <input
                                    id="profile_image_{{ $previewKey }}"
                                    type="file"
                                    name="profile_image"
                                    class="form-control mt-2"
                                    accept="image/*"
                                    data-compress-image="true"
                                    data-preview-target="{{ $previewKey }}"
                                >
                                <div class="upload-note" data-upload-note="{{ $previewKey }}">
                                    อัปโหลดรูปใหม่เพื่อแทนรูปเดิม หรือเลือกตัวเลือกลบรูปด้านล่าง
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_profile_image" value="1" id="remove_profile_image_{{ $staff['id'] }}">
                                    <label class="form-check-label small" for="remove_profile_image_{{ $staff['id'] }}">ลบรูปโปรไฟล์เดิม</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ชื่อพนักงาน <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $staff['name'] }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ชื่อเล่น</label>
                                        <input type="text" name="nickname" class="form-control" value="{{ $staff['nickname'] }}" placeholder="ชื่อเล่น">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">เบอร์โทร</label>
                                        <input type="text" name="phone" class="form-control" value="{{ $staff['phone'] }}" placeholder="0xx-xxx-xxxx">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">ตำแหน่ง</label>
                                        <select name="position" class="form-select">
                                            <option value="" {{ $staff['position'] === '' ? 'selected' : '' }}>เลือกตำแหน่ง</option>
                                            <option value="แคชเชียร์" {{ $staff['position'] === 'แคชเชียร์' ? 'selected' : '' }}>แคชเชียร์</option>
                                            <option value="ผู้จัดการ" {{ $staff['position'] === 'ผู้จัดการ' ? 'selected' : '' }}>ผู้จัดการ</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <label class="form-label small fw-bold">สาขา</label>
                                        <select name="branch_id" class="form-select">
                                            <option value="">ไม่ระบุ</option>
                                            @foreach($branches as $br)
                                            <option value="{{ $br['id'] }}" {{ $staff['branch_id'] == $br['id'] ? 'selected' : '' }}>{{ $br['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold">สถานะ</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $staff['is_active'] ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $staff['is_active'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        @if($staff['linked_user_id'])
                                        <span class="badge-linked">พนักงานคนนี้มีบัญชีผู้ใช้แล้ว</span>
                                        @else
                                        <span class="badge-unlinked">ยังไม่มีบัญชีผู้ใช้ผูกกับพนักงานคนนี้</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="bi bi-save2 me-1"></i>บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @endif
</div>
@endsection

@push('scripts')
@include('masseuse.partials.upload-script')
<script>
    function pfOpenModal(modalId) {
        var modalEl = document.getElementById(modalId);
        if (!modalEl) return;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            modalEl.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');

            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show pf-fallback';
            document.body.appendChild(backdrop);
        }
    }
</script>
@endpush
