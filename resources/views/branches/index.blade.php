@extends('layouts.main')

@section('title', 'จัดการสาขา - PlayFlow')
@section('page_title', 'จัดการสาขา')
@section('page_subtitle', 'Branch Management')

@push('head')
<style>
    .branches-page .card { border-radius: 1.05rem; }
    .branches-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }
    .branches-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }
    .branches-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }
    .branches-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }
    .branches-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
        font-size: 0.82rem;
        white-space: nowrap;
    }
    .branches-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }
    .branches-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }
    .branches-page .context-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.3rem 0.78rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f67bf;
        background: rgba(31, 115, 224, 0.1);
    }
</style>
@endpush

@section('content')
@php
    $activeShopName = (string) ($activeShop->name ?? '');
@endphp
<div class="row g-3 branches-page">
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
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-0">ยังไม่พบตาราง branches ในฐานข้อมูล</div>
        </div>
    @elseif(!($shopSelected ?? true))
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-0 d-flex flex-column gap-2">
                <div class="fw-bold">กรุณาเลือกร้านจากพอร์ทัลก่อน</div>
                <div>เมนูสาขาจะอ้างอิงตามร้านที่คุณเลือกอยู่ในพอร์ทัลร้าน</div>
                <div>
                    <a href="{{ route('system.shops.index') }}" class="btn gradient-btn rounded-pill px-4">
                        <i class="bi bi-shop-window me-1"></i>ไปพอร์ทัลร้าน
                    </a>
                </div>
            </div>
        </div>
    @else
        @if($requiresBranchSetup ?? false)
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-0">
                    <div class="fw-bold mb-1">เริ่มต้นร้านนี้ด้วยการสร้างสาขาแรก</div>
                    <div>บัญชีเจ้าของร้านถูกผูกร้านเรียบร้อยแล้ว ขั้นตอนถัดไปคือเพิ่มสาขาแรกของร้านนี้ก่อน เพื่อใช้สร้างพนักงานและผู้ใช้งานอื่น ๆ</div>
                </div>
            </div>
        @endif
        <div class="col-12">
            <div class="card border-0 shadow-sm hero-card">
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                                <div class="section-title mb-0">จัดการสาขา</div>
                                @if($activeShopName !== '')
                                    <div class="context-pill"><i class="bi bi-shop-window"></i> ร้าน {{ $activeShopName }}</div>
                                @endif
                                <button type="button" class="btn gradient-btn rounded-3 w-100" onclick="pfOpenModal('addBranchModal')">
                                    <i class="bi bi-plus-lg me-1"></i>เพิ่มสาขาใหม่
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <div class="soft-box">
                                <form method="GET" action="{{ route('branches.index') }}" class="row g-2 align-items-end">
                                    <div class="col-12 col-md-8">
                                        <label class="form-label small fw-bold">ค้นหาสาขา</label>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อสาขา, ที่อยู่, เบอร์โทร">
                                    </div>
                                    <div class="col-12 col-md-4 d-grid">
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
                    <h6 class="fw-bold mb-0">รายการสาขา</h6>
                    <span class="badge-soft">{{ count($branches) }} สาขา</span>
                </div>
                <div class="card-body p-2 p-lg-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อสาขา</th>
                                    <th>ที่อยู่</th>
                                    <th>เบอร์โทร</th>
                                    <th>พนักงาน</th>
                                    <th>สถานะ</th>
                                    <th class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $idx => $branch)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <form id="branch-form-{{ $branch['id'] }}" method="POST" action="{{ route('branches.update', ['branchId' => $branch['id']]) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" class="form-control" value="{{ $branch['name'] }}" required>
                                            </form>
                                        </td>
                                        <td><input form="branch-form-{{ $branch['id'] }}" type="text" name="address" class="form-control" value="{{ $branch['address'] }}"></td>
                                        <td><input form="branch-form-{{ $branch['id'] }}" type="text" name="phone" class="form-control" value="{{ $branch['phone'] }}"></td>
                                        <td>{{ $branch['staff_count'] + $branch['user_count'] }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input form="branch-form-{{ $branch['id'] }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $branch['is_active'] ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button form="branch-form-{{ $branch['id'] }}" type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">บันทึก</button>
                                                @if($canManageAllBranches)
                                                    <form method="POST" action="{{ route('branches.destroy', ['branchId' => $branch['id']]) }}" onsubmit="return confirm('ยืนยันลบสาขานี้?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2"><i class="bi bi-trash3"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">ยังไม่มีข้อมูลสาขาในร้านนี้</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade pf-modal" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addBranchModalLabel"><i class="bi bi-building-fill me-2"></i>เพิ่มสาขาใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('branches.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">ชื่อสาขา <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">ที่อยู่</label>
                                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">เบอร์โทร</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn gradient-btn rounded-pill px-4">เพิ่มสาขา</button>
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
</script>
@endpush
