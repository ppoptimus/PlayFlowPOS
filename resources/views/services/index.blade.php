@extends('layouts.main')

@section('title', 'จัดการบริการ - PlayFlow')
@section('page_title', 'จัดการบริการ')

@push('head')
<style>
    .services-page .card { border-radius: 1.05rem; }

    .services-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .services-page .section-title { font-weight: 700; color: #1e5f9d; margin-bottom: 0.55rem; }

    .services-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .services-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }

    .services-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .services-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
        transition: all 0.2s;
    }
    .services-page .gradient-btn-warning {
        background: linear-gradient(135deg, #f0a52d, #b99134) !important;
        border-color: #f0a52d !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(181, 131, 21, 0.24);
        transition: all 0.2s;
    }
    .services-page .gradient-btn:hover, .services-page .gradient-btn-warning:hover {
        filter: brightness(0.96);
        transform: translateY(-1px);
    }

    .services-page .badge-soft {
        border-radius: 999px; padding: 0.26rem 0.6rem;
        font-size: 0.75rem; font-weight: 700;
        color: #0f65b8; background: rgba(45, 143, 240, 0.12);
    }

    .services-page .badge-cat {
        border-radius: 999px; padding: 0.24rem 0.58rem;
        font-size: 0.72rem; font-weight: 700;
        color: #0c907d; background: rgba(20, 184, 154, 0.14);
    }

    .services-page .icon-chip {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.9rem; height: 1.9rem; border-radius: 999px;
        font-size: 0.95rem; box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }
    .services-page .icon-chip i { font-size: 0.9rem; }
    .services-page .icon-chip--blue { color: #0f67bf; background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16)); }
    .services-page .icon-chip--mint { color: #0c907d; background: linear-gradient(145deg, rgba(20, 184, 154, 0.26), rgba(111, 222, 203, 0.16)); }
    .services-page .icon-chip--violet { color: #6d59d8; background: linear-gradient(145deg, rgba(129, 96, 255, 0.22), rgba(180, 159, 255, 0.14)); }

    .services-page .pf-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.1rem;
        box-shadow: 0 24px 48px rgba(14, 60, 120, 0.18);
    }
    .services-page .pf-modal .modal-header {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff; border-radius: 1.1rem 1.1rem 0 0; border-bottom: none;
    }
    .services-page .pf-modal .modal-header .btn-close { filter: brightness(0) invert(1); }
    .services-page .pf-modal .modal-body { background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%); }
    .services-page .pf-modal .modal-footer { border-top: 1px solid rgba(31, 115, 224, 0.1); }

    .services-page .table td .form-control,
    .services-page .table td .form-select { font-size: 0.85rem; padding: 0.28rem 0.45rem; }

    /* ─── Mobile Card ─── */
    .services-page .mobile-service-card {
        border: 1px solid rgba(31,115,224,0.12); border-radius: 0.85rem;
        background: #fff; padding: 0.85rem;
        box-shadow: 0 4px 10px rgba(14,72,133,0.06);
    }
    .services-page .mobile-service-card .card-title { font-weight: 700; color: #1e3a5f; font-size: 0.95rem; }
    .services-page .mobile-service-card .meta { font-size: 0.78rem; color: #5c728a; }
    .services-page .mobile-service-card .meta strong { color: #1e5f9d; }

    @media (max-width: 767.98px) {
        .services-page .card-body { padding: 0.9rem; }
        .services-page .form-label, .services-page .small { font-size: 0.78rem; }
        .services-page .form-control, .services-page .btn { font-size: 0.9rem; }
    }
</style>
@endpush

@section('content')
<div class="row g-3 services-page">
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

    {{-- ═══ Hero Card ═══ --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                            <div class="section-title mb-0">จัดการข้อมูลหลัก</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn gradient-btn rounded-3 flex-fill" onclick="pfOpenModal('addServiceModal')">
                                    <i class="fa-solid fa-plus me-1"></i>เพิ่มบริการ
                                </button>
                                <button type="button" class="btn gradient-btn-warning rounded-3 flex-fill" onclick="pfOpenModal('addCategoryModal')">
                                    <i class="fa-solid fa-plus me-1"></i>เพิ่มหมวดหมู่
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="soft-box">
                            <form method="GET" action="{{ route('services.index') }}" class="row g-2 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-bold">ค้นหาบริการ</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="fa-solid fa-magnifying-glass"></i></span></span>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อบริการ เช่น นวดไทย">
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-bold">หมวดหมู่</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">ทุกหมวดหมู่</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ ($categoryFilter ?? null) == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 d-grid">
                                    <button type="submit" class="btn gradient-btn rounded-3"><i class="fa-solid fa-filter me-1"></i>กรอง</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Service Table ═══ --}}
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--blue me-2"><i class="fa-solid fa-spa"></i></span>รายการบริการ</h6>
                <span class="badge-soft">{{ count($services) }} รายการ</span>
            </div>
            <div class="card-body p-2 p-lg-3">

                {{-- Desktop Table --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">ชื่อบริการ</th>
                                <th style="min-width: 100px;">หมวดหมู่</th>
                                <th style="min-width: 80px;">ระยะเวลา</th>
                                <th style="min-width: 90px;">ราคา (฿)</th>
                                <th style="min-width: 70px;">สถานะ</th>
                                <th class="text-end" style="min-width: 140px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            <tr>
                                <td>
                                    <form id="svc-form-{{ $service['id'] }}" method="POST" action="{{ route('services.update', ['serviceId' => $service['id']]) }}">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" class="form-control" value="{{ $service['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <select form="svc-form-{{ $service['id'] }}" name="category_id" class="form-select">
                                        <option value="">-</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ $service['category_id'] == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input form="svc-form-{{ $service['id'] }}" type="number" name="duration_minutes" class="form-control" value="{{ $service['duration_minutes'] }}" min="1" required>
                                </td>
                                <td>
                                    <input form="svc-form-{{ $service['id'] }}" type="number" step="0.01" min="0" name="price" class="form-control" value="{{ $service['price'] }}" required>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input form="svc-form-{{ $service['id'] }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $service['is_active'] ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <button form="svc-form-{{ $service['id'] }}" type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก">
                                            <i class="fa-solid fa-floppy-disk me-1"></i>บันทึก
                                        </button>
                                        <form method="POST" action="{{ route('services.destroy', ['serviceId' => $service['id']]) }}" onsubmit="return confirm('ยืนยันลบบริการนี้?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">ยังไม่มีบริการ</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="d-flex flex-column gap-2 d-md-none">
                    @forelse($services as $service)
                    <div class="mobile-service-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="card-title">{{ $service['name'] }}</div>
                                @php
                                    $catName = '-';
                                    foreach($categories as $c) { if($c['id'] == $service['category_id']) { $catName = $c['name']; break; } }
                                @endphp
                                <span class="badge-cat">{{ $catName }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-2" onclick="pfOpenModal('editServiceModal{{ $service['id'] }}')" title="แก้ไข">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" action="{{ route('services.destroy', ['serviceId' => $service['id']]) }}" onsubmit="return confirm('ยืนยันลบบริการนี้?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="d-flex gap-3 meta">
                            <span><i class="fa-solid fa-clock me-1"></i><strong>{{ $service['duration_minutes'] }}</strong> นาที</span>
                            <span><i class="fa-solid fa-tag me-1"></i><strong>{{ number_format($service['price'], 2) }}</strong> ฿</span>
                            <span>
                                @if($service['is_active'])
                                    <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>เปิด</span>
                                @else
                                    <span class="text-danger fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i>ปิด</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Edit Modal for Mobile --}}
                    <div class="modal fade pf-modal services-page" id="editServiceModal{{ $service['id'] }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen me-2"></i>แก้ไขบริการ</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('services.update', ['serviceId' => $service['id']]) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">ชื่อบริการ <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $service['name'] }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">หมวดหมู่</label>
                                                <select name="category_id" class="form-select">
                                                    <option value="">ไม่ระบุ</option>
                                                    @foreach($categories as $cat)
                                                    <option value="{{ $cat['id'] }}" {{ $service['category_id'] == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">ระยะเวลา (นาที) <span class="text-danger">*</span></label>
                                                <input type="number" name="duration_minutes" class="form-control" value="{{ $service['duration_minutes'] }}" min="1" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">ราคา (฿) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ $service['price'] }}" required>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $service['id'] }}" {{ $service['is_active'] ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="editActive{{ $service['id'] }}">เปิดใช้งาน</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i>บันทึก</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">ยังไม่มีบริการ</div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

    {{-- ═══ Right Sidebar: หมวดหมู่บริการ ═══ --}}
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--violet me-2"><i class="fa-solid fa-tags"></i></span>หมวดหมู่บริการ</h6>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="pfOpenModal('addCategoryModal')">
                    <i class="fa-solid fa-plus me-1"></i>เพิ่ม
                </button>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="d-flex flex-column gap-2" style="max-height: 400px; overflow: auto;">
                    @forelse($categories as $cat)
                    <div class="soft-box p-2 d-flex align-items-center gap-2">
                        <form method="POST" action="{{ route('services.categories.update', ['categoryId' => $cat['id']]) }}" class="d-flex gap-2 flex-grow-1">
                            @csrf @method('PUT')
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat['name'] }}" required>
                            <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก"><i class="fa-solid fa-check"></i></button>
                        </form>
                        <form method="POST" action="{{ route('services.categories.destroy', ['categoryId' => $cat['id']]) }}" onsubmit="return confirm('ยืนยันลบหมวดหมู่?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </div>
                    @empty
                    <div class="text-center text-muted py-2 small">ยังไม่มีหมวดหมู่</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Modal: เพิ่มบริการ ═══ --}}
    <div class="modal fade pf-modal services-page" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addServiceModalLabel"><i class="fa-solid fa-spa me-2"></i>เพิ่มบริการใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('services.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">ชื่อบริการ <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น นวดแผนไทย, นวดน้ำมัน" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">หมวดหมู่</label>
                                <select name="category_id" class="form-select">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat['id'] }}" {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">ระยะเวลา (นาที) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 60) }}" min="1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">ราคา (฿) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', '0') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i>เพิ่มบริการ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══ Modal: เพิ่มหมวดหมู่ ═══ --}}
    <div class="modal fade pf-modal services-page" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel"><i class="fa-solid fa-tags me-2"></i>เพิ่มหมวดหมู่บริการ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('services.categories.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <label class="form-label small fw-bold">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น นวดไทย, สปา, สครับ" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i>เพิ่มหมวดหมู่</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
