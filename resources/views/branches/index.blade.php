@extends('layouts.main')

@section('title', 'จัดการสาขา - PlayFlow')
@section('page_title', 'จัดการสาขา')

@push('head')
<style>
    .branches-page .card {
        border-radius: 1.05rem;
    }

    .branches-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .branches-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }

    .branches-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
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

    .branches-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .branches-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .branches-page .badge-active {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #0c907d;
        background: rgba(20, 184, 154, 0.14);
    }

    .branches-page .badge-inactive {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #a14545;
        background: rgba(220, 53, 69, 0.1);
    }

    .branches-page .icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }

    .branches-page .icon-chip i {
        font-size: 0.9rem;
    }

    .branches-page .icon-chip--blue {
        color: #0f67bf;
        background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16));
    }

    .branches-page .icon-chip--mint {
        color: #0c907d;
        background: linear-gradient(145deg, rgba(20, 184, 154, 0.26), rgba(111, 222, 203, 0.16));
    }

    .branches-page .stat-chip {
        font-size: 0.72rem;
        font-weight: 600;
        color: #4a7aab;
        background: rgba(31, 115, 224, 0.08);
        border-radius: 999px;
        padding: 0.18rem 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .branches-page .pf-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.1rem;
        box-shadow: 0 24px 48px rgba(14, 60, 120, 0.18);
    }

    .branches-page .pf-modal .modal-header {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        border-radius: 1.1rem 1.1rem 0 0;
        border-bottom: none;
    }

    .branches-page .pf-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .branches-page .pf-modal .modal-body {
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .branches-page .pf-modal .modal-footer {
        border-top: 1px solid rgba(31, 115, 224, 0.1);
    }

    .branches-page .table td .form-control,
    .branches-page .table td .form-select {
        font-size: 0.85rem;
        padding: 0.28rem 0.45rem;
    }

    @media (max-width: 767.98px) {
        .branches-page .card-body {
            padding: 0.9rem;
        }

        .branches-page .form-label,
        .branches-page .small {
            font-size: 0.78rem;
        }

        .branches-page .form-control,
        .branches-page .btn {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 branches-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง branches</div>
            <div>กรุณารัน <code>php artisan migrate</code> ก่อนใช้งานหน้านี้</div>
        </div>
    </div>
    @else

    {{-- ═══ Hero Card ═══ --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                            <div class="section-title mb-0">จัดการสาขา</div>
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
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="bi bi-search"></i></span></span>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อสาขา, ที่อยู่, เบอร์โทร">
                                    </div>
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

    {{-- ═══ Branch Table ═══ --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--blue me-2"><i class="bi bi-building-fill"></i></span>รายการสาขา</h6>
                <span class="badge-soft">{{ count($branches) }} สาขา</span>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 50px;">#</th>
                                <th style="min-width: 180px;">ชื่อสาขา</th>
                                <th style="min-width: 220px;">ที่อยู่</th>
                                <th style="min-width: 130px;">เบอร์โทร</th>
                                <th style="min-width: 100px;">พนักงาน</th>
                                <th style="min-width: 70px;">สถานะ</th>
                                <th class="text-end" style="min-width: 140px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $idx => $branch)
                            <tr>
                                <td class="text-muted small">{{ $idx + 1 }}</td>
                                <td>
                                    <form id="branch-form-{{ $branch['id'] }}" method="POST" action="{{ route('branches.update', ['branchId' => $branch['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control" value="{{ $branch['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="branch-form-{{ $branch['id'] }}" type="text" name="address" class="form-control" value="{{ $branch['address'] }}" placeholder="-">
                                </td>
                                <td>
                                    <input form="branch-form-{{ $branch['id'] }}" type="text" name="phone" class="form-control" value="{{ $branch['phone'] }}" placeholder="-">
                                </td>
                                <td>
                                    <span class="stat-chip"><i class="bi bi-people"></i> {{ $branch['staff_count'] + $branch['user_count'] }}</span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input form="branch-form-{{ $branch['id'] }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $branch['is_active'] ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <button form="branch-form-{{ $branch['id'] }}" type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก">
                                            <i class="bi bi-save2 me-1"></i>บันทึก
                                        </button>
                                        <form method="POST" action="{{ route('branches.destroy', ['branchId' => $branch['id']]) }}" onsubmit="return confirm('ยืนยันลบสาขานี้?')">
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
                                <td colspan="7" class="text-center text-muted py-4">ยังไม่มีข้อมูลสาขา</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Modal: เพิ่มสาขา ═══ --}}
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
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น สาขาสยาม" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">ที่อยู่</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="ที่อยู่สาขา">{{ old('address') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">เบอร์โทร</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="02-xxx-xxxx">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i>เพิ่มสาขา</button>
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
</script>
@endpush
