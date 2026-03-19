@extends('layouts.main')

@section('title', 'สินค้า & สต็อก - PlayFlow')
@section('page_title', 'สินค้า & สต็อก')

@push('head')
<style>
    .products-page .card {
        border-radius: 1.05rem;
    }

    .products-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .products-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }

    .products-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .products-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }

    .products-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .products-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }
    .products-page .gradient-btn-warning {
        background: linear-gradient(135deg, #f0a52d, #b99134ff) !important;
        border-color: #f0a52d !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }

    .products-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .products-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .products-page .badge-retail {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #0c907d;
        background: rgba(20, 184, 154, 0.14);
    }

    .products-page .badge-internal {
        border-radius: 999px;
        padding: 0.24rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #6d59d8;
        background: rgba(129, 96, 255, 0.12);
    }

    .products-page .badge-low-stock {
        border-radius: 999px;
        padding: 0.22rem 0.52rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #d35400;
        background: rgba(230, 126, 34, 0.12);
    }

    .products-page .icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }

    .products-page .icon-chip i {
        font-size: 0.9rem;
    }

    .products-page .icon-chip--blue {
        color: #0f67bf;
        background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16));
    }

    .products-page .icon-chip--mint {
        color: #0c907d;
        background: linear-gradient(145deg, rgba(20, 184, 154, 0.26), rgba(111, 222, 203, 0.16));
    }

    .products-page .icon-chip--violet {
        color: #6d59d8;
        background: linear-gradient(145deg, rgba(129, 96, 255, 0.22), rgba(180, 159, 255, 0.14));
    }

    .products-page .icon-chip--pink {
        color: #bf4d8a;
        background: linear-gradient(145deg, rgba(242, 113, 188, 0.24), rgba(255, 166, 219, 0.16));
    }

    .products-page .pf-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.1rem;
        box-shadow: 0 24px 48px rgba(14, 60, 120, 0.18);
    }

    .products-page .pf-modal .modal-header {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        border-radius: 1.1rem 1.1rem 0 0;
        border-bottom: none;
    }

    .products-page .pf-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .products-page .pf-modal .modal-body {
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .products-page .pf-modal .modal-footer {
        border-top: 1px solid rgba(31, 115, 224, 0.1);
    }

    .products-page .table td .form-control,
    .products-page .table td .form-select {
        font-size: 0.85rem;
        padding: 0.28rem 0.45rem;
    }

    @media (max-width: 767.98px) {
        .products-page .card-body {
            padding: 0.9rem;
        }

        .products-page .form-label,
        .products-page .small {
            font-size: 0.78rem;
        }

        .products-page .form-control,
        .products-page .btn {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 products-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง products</div>
            <div>กรุณารัน <code>php artisan migrate</code> ก่อนใช้งานหน้านี้</div>
        </div>
    </div>
    @else

    {{-- ═══ Hero Card: Search / Filter + Action Buttons ═══ --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <div class="soft-box d-flex flex-column gap-2 justify-content-center">
                            <div class="section-title mb-0">จัดการข้อมูลหลัก</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn gradient-btn rounded-3 flex-fill" onclick="pfOpenModal('addProductModal')">
                                    <i class="fa-solid fa-plus me-1"></i>เพิ่มสินค้า
                                </button>
                                <button type="button" class="btn gradient-btn-warning rounded-3 flex-fill" onclick="pfOpenModal('addCategoryModal')">
                                    <i class="fa-solid fa-plus me-1"></i>เพิ่มหมวดหมู่
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="soft-box">
                            <form method="GET" action="{{ route('products') }}" class="row g-2 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-bold">ค้นหาสินค้า</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="fa-solid fa-magnifying-glass"></i></span></span>
                                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อ, SKU, บาร์โค้ด">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold">ประเภท</label>
                                    <select name="type" class="form-select">
                                        <option value="">ทุกประเภท</option>
                                        <option value="retail" {{ $typeFilter === 'retail' ? 'selected' : '' }}>ขายปลีก</option>
                                        <option value="internal" {{ $typeFilter === 'internal' ? 'selected' : '' }}>ใช้ภายใน</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold">หมวดหมู่</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">ทุกหมวดหมู่</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ ($categoryFilter ?? null) == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 d-grid">
                                    <button type="submit" class="btn gradient-btn rounded-3"><i class="fa-solid fa-filter me-1"></i>กรอง</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Product Table ═══ --}}
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--blue me-2"><i class="fa-solid fa-box-open"></i></span>รายการสินค้า</h6>
                <span class="badge-soft">{{ count($products) }} รายการ</span>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">ชื่อสินค้า</th>
                                <th style="min-width: 90px;">SKU</th>
                                <th style="min-width: 100px;">ประเภท</th>
                                <th style="min-width: 100px;">หมวดหมู่</th>
                                <th style="min-width: 80px;">ต้นทุน</th>
                                <th style="min-width: 80px;">ราคาขาย</th>
                                <th style="min-width: 75px;">สต็อก</th>
                                <th style="min-width: 75px;">ขั้นต่ำ</th>
                                <th style="min-width: 70px;">สถานะ</th>
                                <th class="text-end" style="min-width: 140px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <form id="product-form-{{ $product['id'] }}" method="POST" action="{{ route('products.update', ['productId' => $product['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control" value="{{ $product['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="text" name="sku" class="form-control" value="{{ $product['sku'] }}" placeholder="-">
                                </td>
                                <td>
                                    <select form="product-form-{{ $product['id'] }}" name="type" class="form-select">
                                        <option value="retail" {{ $product['type'] === 'retail' ? 'selected' : '' }}>ขายปลีก</option>
                                        <option value="internal" {{ $product['type'] === 'internal' ? 'selected' : '' }}>ใช้ภายใน</option>
                                    </select>
                                </td>
                                <td>
                                    <select form="product-form-{{ $product['id'] }}" name="category_id" class="form-select">
                                        <option value="">-</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ $product['category_id'] == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="number" step="0.01" min="0" name="cost_price" class="form-control" value="{{ $product['cost_price'] }}" required>
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="number" step="0.01" min="0" name="sell_price" class="form-control" value="{{ $product['sell_price'] }}" required>
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="number" min="0" name="stock_qty" class="form-control" value="{{ $product['stock_qty'] }}">
                                    @if($product['min_stock'] > 0 && $product['stock_qty'] < $product['min_stock'])
                                    <span class="badge-low-stock mt-1 d-inline-block">ต่ำ!</span>
                                    @endif
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="number" min="0" name="min_stock" class="form-control" value="{{ $product['min_stock'] }}">
                                </td>
                                <td>
                                    <input form="product-form-{{ $product['id'] }}" type="hidden" name="barcode" value="{{ $product['barcode'] }}">
                                    <div class="form-check form-switch">
                                        <input form="product-form-{{ $product['id'] }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $product['is_active'] ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <button form="product-form-{{ $product['id'] }}" type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก">
                                            <i class="fa-solid fa-floppy-disk me-1"></i>บันทึก
                                        </button>
                                        <form method="POST" action="{{ route('products.destroy', ['productId' => $product['id']]) }}" onsubmit="return confirm('ยืนยันลบสินค้านี้?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">ยังไม่มีสินค้า</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Right Sidebar: Categories + Low Stock ═══ --}}
    <div class="col-12 col-xl-4">
        <div class="d-flex flex-column gap-3">

            {{-- Categories --}}
            <!-- <div class="card border-0 shadow-sm table-card">
                <div class="card-header bg-white border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--violet me-2"><i class="fa-solid fa-tags"></i></span>หมวดหมู่สินค้า</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="pfOpenModal('addCategoryModal')">
                        <i class="fa-solid fa-plus me-1"></i>เพิ่ม
                    </button>
                </div>
                <div class="card-body p-2 p-lg-3">
                    <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow: auto;">
                        @forelse($categories as $cat)
                        <div class="soft-box p-2 d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('products.categories.update', ['categoryId' => $cat['id']]) }}" class="d-flex gap-2 flex-grow-1">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat['name'] }}" required>
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="บันทึก"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <form method="POST" action="{{ route('products.categories.destroy', ['categoryId' => $cat['id']]) }}" onsubmit="return confirm('ยืนยันลบหมวดหมู่?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                        @empty
                        <div class="text-center text-muted py-2 small">ยังไม่มีหมวดหมู่</div>
                        @endforelse
                    </div>
                </div>
            </div> -->

            {{-- Low Stock Alert --}}
            <div class="card border-0 shadow-sm table-card">
                <div class="card-header bg-white border-0 pt-3 pb-2">
                    <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--pink me-2"><i class="fa-solid fa-triangle-exclamation"></i></span>สินค้าสต็อกต่ำ</h6>
                </div>
                <div class="card-body p-2 p-lg-3" style="max-height: 360px; overflow: auto;">
                    <div class="d-flex flex-column gap-2">
                        @forelse($lowStockProducts as $lowItem)
                        <div class="soft-box p-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-bold small">{{ $lowItem['name'] }}</div>
                                <span class="{{ $lowItem['type'] === 'retail' ? 'badge-retail' : 'badge-internal' }}">{{ $lowItem['type'] === 'retail' ? 'ขายปลีก' : 'ภายใน' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold small">คงเหลือ {{ number_format($lowItem['stock_qty']) }}</span>
                                    <span class="text-muted small"> / ขั้นต่ำ {{ number_format($lowItem['min_stock']) }}</span>
                                </div>
                                <form method="POST" action="{{ route('products.adjust-stock', ['productId' => $lowItem['id']]) }}" class="d-flex gap-1 align-items-center">
                                    @csrf
                                    <input type="number" name="adjust_qty" class="form-control form-control-sm" style="width: 70px;" placeholder="+10" required>
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2" title="เพิ่มสต็อก"><i class="fa-solid fa-plus"></i></button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3 small">
                            <i class="fa-solid fa-circle-check text-success me-1"></i>สต็อกทุกรายการปกติ
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══ Modal: เพิ่มสินค้า ═══ --}}
    <div class="modal fade pf-modal" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addProductModalLabel"><i class="fa-solid fa-box-open me-2"></i>เพิ่มสินค้าใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น น้ำมันนวดอโรมา" required>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="OIL-001">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">บาร์โค้ด</label>
                                <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}" placeholder="8850...">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">ประเภท <span class="text-danger">*</span></label>
                                <select name="type" class="form-select">
                                    <option value="retail" {{ old('type') === 'internal' ? '' : 'selected' }}>ขายปลีก</option>
                                    <option value="internal" {{ old('type') === 'internal' ? 'selected' : '' }}>ใช้ภายใน</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">หมวดหมู่</label>
                                <select name="category_id" class="form-select">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat['id'] }}" {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">ต้นทุน (฿) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="cost_price" class="form-control" value="{{ old('cost_price', '0') }}" required>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-bold">ราคาขาย (฿) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="sell_price" class="form-control" value="{{ old('sell_price', '0') }}" required>
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label small fw-bold">สต็อกเริ่มต้น</label>
                                <input type="number" min="0" name="stock_qty" class="form-control" value="{{ old('stock_qty', '0') }}">
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label small fw-bold">สต็อกขั้นต่ำ (แจ้งเตือน)</label>
                                <input type="number" min="0" name="min_stock" class="form-control" value="{{ old('min_stock', '0') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i>เพิ่มสินค้า</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══ Modal: เพิ่มหมวดหมู่ ═══ --}}
    <div class="modal fade pf-modal" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel"><i class="fa-solid fa-tags me-2"></i>เพิ่มหมวดหมู่ใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('products.categories.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <label class="form-label small fw-bold">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น น้ำมันนวด, ลูกประคบ" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn gradient-btn rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i>เพิ่มหมวดหมู่</button>
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
