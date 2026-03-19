@extends('layouts.main')

@section('title', 'จัดการผู้ใช้งาน - PlayFlow')
@section('page_title', 'จัดการผู้ใช้งาน')
@section('page_subtitle', 'User Accounts')

@push('head')
<style>
    .users-page .card {
        border-radius: 1.05rem;
    }

    .users-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .users-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }

    .users-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .users-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }

    .users-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .users-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }

    .users-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .users-page .gradient-btn-warning {
        background: linear-gradient(135deg, #f0a52d, #b99134) !important;
        border-color: #f0a52d !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(181, 121, 21, 0.24);
    }

    .users-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .users-page .role-pill {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .users-page .role-pill.is-super {
        color: #d35400;
        background: rgba(230, 126, 34, 0.12);
    }

    .users-page .role-pill.is-manager {
        color: #6d59d8;
        background: rgba(129, 96, 255, 0.12);
    }

    .users-page .role-pill.is-cashier {
        color: #0c907d;
        background: rgba(20, 184, 154, 0.14);
    }

    .users-page .role-pill.is-masseuse {
        color: #2c79b8;
        background: rgba(31, 115, 224, 0.1);
    }

    .users-page .icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }

    .users-page .icon-chip--blue {
        color: #0f67bf;
        background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16));
    }

    .users-page .pf-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.1rem;
        box-shadow: 0 24px 48px rgba(14, 60, 120, 0.18);
    }

    .users-page .pf-modal .modal-header {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        border-radius: 1.1rem 1.1rem 0 0;
        border-bottom: none;
    }

    .users-page .pf-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .users-page .pf-modal .modal-body {
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .users-page .pf-modal .modal-footer {
        border-top: 1px solid rgba(31, 115, 224, 0.1);
    }

    .users-page .table td .form-select {
        font-size: 0.85rem;
        padding: 0.28rem 0.45rem;
    }

    .users-page .last-login-text {
        font-size: 0.72rem;
        color: #6b7f93;
    }

    .users-page .user-summary {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .users-page .user-avatar {
        width: 46px;
        height: 46px;
        border-radius: 999px;
        object-fit: cover;
        border: 2px solid rgba(31, 115, 224, 0.14);
        background: #ffffff;
        flex-shrink: 0;
    }

    .users-page .user-copy {
        min-width: 0;
    }

    .users-page .user-name {
        font-weight: 700;
        color: #1f456c;
        line-height: 1.15;
    }

    .users-page .user-meta {
        color: #6b7f93;
        font-size: 0.78rem;
        line-height: 1.2;
    }

    .users-page .staff-option-card {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 1rem;
        padding: 0.85rem;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .users-page .staff-option-row {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .users-page .staff-option-avatar {
        width: 52px;
        height: 52px;
        border-radius: 999px;
        object-fit: cover;
        border: 2px solid rgba(31, 115, 224, 0.14);
    }

    .users-page .staff-option-title {
        font-weight: 700;
        color: #1f456c;
        line-height: 1.15;
    }

    .users-page .staff-option-meta {
        color: #6b7f93;
        font-size: 0.78rem;
        line-height: 1.2;
    }

    @media (max-width: 767.98px) {
        .users-page .card-body {
            padding: 0.9rem;
        }

        .users-page .form-label,
        .users-page .small {
            font-size: 0.78rem;
        }

        .users-page .form-select,
        .users-page .btn,
        .users-page .form-control {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $roleClasses = [
        'super_admin' => 'is-super',
        'branch_manager' => 'is-manager',
        'cashier' => 'is-cashier',
        'masseuse' => 'is-masseuse',
    ];
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'branch_manager' => 'ผู้จัดการสาขา',
        'cashier' => 'แคชเชียร์',
        'masseuse' => 'หมอนวด',
    ];
@endphp
<div class="row g-3 users-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง users</div>
            <div>โปรดตรวจสอบฐานข้อมูลจริงของระบบก่อนใช้งานโมดูลนี้</div>
        </div>
    </div>
    @else

    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                            <div class="section-title mb-0">จัดการบัญชีผู้ใช้</div>
                            <button type="button" class="btn gradient-btn rounded-3 w-100" onclick="pfOpenModal('addUserModal')">
                                <i class="bi bi-person-plus-fill me-1"></i>เพิ่มผู้ใช้งานใหม่
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="soft-box">
                            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-bold">ค้นหาผู้ใช้</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="bi bi-search"></i></span></span>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อพนักงาน, ชื่อเล่น, Username">
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
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--blue me-2"><i class="bi bi-shield-lock-fill"></i></span>รายการผู้ใช้งาน</h6>
                <span class="badge-soft">{{ count($users) }} บัญชี</span>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 50px;">#</th>
                                <th style="min-width: 240px;">พนักงาน / Username</th>
                                <th style="min-width: 140px;">บทบาท</th>
                                <th style="min-width: 130px;">สาขา</th>
                                <th style="min-width: 120px;">เข้าระบบล่าสุด</th>
                                <th style="min-width: 80px;">สถานะ</th>
                                <th class="text-end" style="min-width: 180px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $idx => $user)
                            @php
                                $displayName = $user['staff_name'] !== '' ? $user['staff_name'] : '-';
                                $displayMeta = $user['staff_nickname'] !== '' ? $user['staff_nickname'] : ($user['staff_position'] !== '' ? $user['staff_position'] : 'ยังไม่ผูกข้อมูลพนักงาน');
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $idx + 1 }}</td>
                                <td>
                                    <form id="user-form-{{ $user['id'] }}" method="POST" action="{{ route('users.update', ['userId' => $user['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <div class="user-summary">
                                        @if($user['staff_id'])
                                        <img src="{{ $user['staff_avatar'] }}" alt="{{ $displayName }}" class="user-avatar">
                                        @else
                                        <div class="user-avatar d-flex align-items-center justify-content-center text-primary bg-white">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        @endif
                                        <div class="user-copy">
                                            <div class="user-name">{{ $displayName }}</div>
                                            <div class="user-meta">{{ $user['username'] }}</div>
                                            <div class="user-meta">{{ $displayMeta }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select form="user-form-{{ $user['id'] }}" name="role" class="form-select">
                                        @foreach($roles as $role)
                                        <option value="{{ $role['value'] }}" {{ $user['role'] === $role['value'] ? 'selected' : '' }}>{{ $role['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select form="user-form-{{ $user['id'] }}" name="branch_id" class="form-select">
                                        <option value="">-</option>
                                        @foreach($branches as $br)
                                        <option value="{{ $br['id'] }}" {{ $user['branch_id'] == $br['id'] ? 'selected' : '' }}>{{ $br['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @if($user['last_login'])
                                    <span class="last-login-text">{{ \Carbon\Carbon::parse($user['last_login'])->diffForHumans() }}</span>
                                    @else
                                    <span class="last-login-text">ยังไม่เคย</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supportsActiveToggle)
                                    <div class="form-check form-switch">
                                        <input form="user-form-{{ $user['id'] }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $user['is_active'] ? 'checked' : '' }}>
                                    </div>
                                    @else
                                    <span class="badge-soft">ใช้งาน</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <button form="user-form-{{ $user['id'] }}" type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก">
                                            <i class="bi bi-save2 me-1"></i>บันทึก
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-2" title="รีเซ็ตรหัสผ่าน" onclick="pfOpenResetModal({{ $user['id'] }}, '{{ addslashes($displayName) }}')">
                                            <i class="bi bi-shield-lock-fill"></i>
                                        </button>
                                        @if($user['id'] !== (int) (auth()->user()->id ?? 0))
                                        <form method="POST" action="{{ route('users.destroy', ['userId' => $user['id']]) }}" onsubmit="return confirm('ยืนยันลบผู้ใช้ {{ addslashes($displayName) }} ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">ยังไม่มีผู้ใช้งาน</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade pf-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel"><i class="bi bi-person-plus-fill me-2"></i>เพิ่มผู้ใช้งานใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @if(empty($staffOptions))
                        <div class="alert alert-warning border-0 rounded-4 mb-0">
                            <div class="fw-bold mb-1">ยังไม่มีพนักงานที่พร้อมสร้างบัญชี</div>
                            <div>ต้องเพิ่มพนักงานในโมดูลพนักงานก่อน และพนักงาน 1 คนจะมีได้ 1 บัญชีผู้ใช้</div>
                        </div>
                        @else
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">เลือกพนักงาน <span class="text-danger">*</span></label>
                                <select name="staff_id" id="staff_id" class="form-select" required>
                                    <option value="">เลือกพนักงานจากระบบ</option>
                                    @foreach($staffOptions as $staff)
                                    <option
                                        value="{{ $staff['id'] }}"
                                        data-name="{{ $staff['name'] }}"
                                        data-position="{{ $staff['position'] }}"
                                        data-branch-id="{{ $staff['branch_id'] }}"
                                        data-branch-name="{{ $staff['branch_name'] }}"
                                        data-avatar="{{ $staff['avatar'] }}"
                                        {{ old('staff_id') == $staff['id'] ? 'selected' : '' }}
                                    >
                                        {{ $staff['name'] }}@if($staff['nickname']) ({{ $staff['nickname'] }}) @endif - {{ $staff['branch_name'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="staff-option-card">
                                    <div class="staff-option-row">
                                        <img id="selectedStaffAvatar" src="" alt="staff preview" class="staff-option-avatar">
                                        <div>
                                            <div id="selectedStaffName" class="staff-option-title">ยังไม่ได้เลือกพนักงาน</div>
                                            <div id="selectedStaffMeta" class="staff-option-meta">เมื่อเลือกพนักงาน ระบบจะผูกบัญชีนี้กับพนักงานคนนั้นโดยตรง</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="เช่น cashier01" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">รหัสผ่าน <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 4 ตัวอักษร" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">บทบาท <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles as $role)
                                    <option value="{{ $role['value'] }}" {{ old('role', 'cashier') === $role['value'] ? 'selected' : '' }}>{{ $role['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">สาขา</label>
                                <select name="branch_id" id="user_branch_id" class="form-select">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach($branches as $br)
                                    <option value="{{ $br['id'] }}" {{ old('branch_id') == $br['id'] ? 'selected' : '' }}>{{ $br['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1">ค่าเริ่มต้นจะอ้างอิงจากสาขาของพนักงานที่เลือก</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4" {{ empty($staffOptions) ? 'disabled' : '' }}><i class="bi bi-plus-lg me-1"></i>เพิ่มผู้ใช้</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade pf-modal" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="resetPasswordModalLabel"><i class="bi bi-shield-lock-fill me-2"></i>รีเซ็ตรหัสผ่าน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="resetPasswordForm" method="POST" action="">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-3 mb-3">
                            <i class="bi bi-info-circle me-1"></i> กำลังรีเซ็ตรหัสผ่านให้ <strong id="resetUserName"></strong>
                        </div>
                        <div>
                            <label class="form-label small fw-bold">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" placeholder="อย่างน้อย 4 ตัวอักษร" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn-warning rounded-pill px-4"><i class="bi bi-shield-lock-fill me-1"></i>รีเซ็ตรหัสผ่าน</button>
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

    function pfOpenResetModal(userId, userName) {
        var form = document.getElementById('resetPasswordForm');
        var nameEl = document.getElementById('resetUserName');
        if (form) {
            form.action = '/users/' + userId + '/reset-password';
        }
        if (nameEl) {
            nameEl.textContent = userName;
        }
        pfOpenModal('resetPasswordModal');
    }

    (function () {
        var selectEl = document.getElementById('staff_id');
        var nameEl = document.getElementById('selectedStaffName');
        var metaEl = document.getElementById('selectedStaffMeta');
        var avatarEl = document.getElementById('selectedStaffAvatar');
        var branchEl = document.getElementById('user_branch_id');
        if (!selectEl) return;

        function syncSelectedStaff() {
            var option = selectEl.options[selectEl.selectedIndex];
            if (!option || !option.value) {
                if (nameEl) nameEl.textContent = 'ยังไม่ได้เลือกพนักงาน';
                if (metaEl) metaEl.textContent = 'เมื่อเลือกพนักงาน ระบบจะผูกบัญชีนี้กับพนักงานคนนั้นโดยตรง';
                if (avatarEl) avatarEl.removeAttribute('src');
                return;
            }

            var staffName = option.getAttribute('data-name') || '-';
            var staffPosition = option.getAttribute('data-position') || '-';
            var branchName = option.getAttribute('data-branch-name') || '-';
            var branchId = option.getAttribute('data-branch-id') || '';
            var avatar = option.getAttribute('data-avatar') || '';

            if (nameEl) nameEl.textContent = staffName;
            if (metaEl) metaEl.textContent = staffPosition + ' | ' + branchName;
            if (avatarEl) avatarEl.src = avatar;
            if (branchEl && branchId && !branchEl.value) {
                branchEl.value = branchId;
            }
        }

        selectEl.addEventListener('change', syncSelectedStaff);
        syncSelectedStaff();
    })();
</script>
@endpush
