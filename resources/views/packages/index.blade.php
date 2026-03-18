@extends('layouts.main')

@section('title', 'แพ็กเกจ - PlayFlow')
@section('page_title', 'จัดการแพ็กเกจ')

@push('head')
<style>
    .packages-page .card {
        border-radius: 1.05rem;
    }

    .packages-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(165deg, #eef9ff 0%, #f6fdff 100%);
        box-shadow: 0 16px 28px rgba(18, 85, 150, 0.1) !important;
    }

    .packages-page .section-title {
        font-weight: 700;
        color: #1e5f9d;
        margin-bottom: 0.55rem;
    }

    .packages-page .soft-box {
        border: 1px solid rgba(31, 115, 224, 0.13);
        border-radius: 0.92rem;
        background: #ffffff;
        box-shadow: 0 7px 14px rgba(14, 72, 133, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .packages-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }

    .packages-page .table thead th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
    }

    .packages-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }

    .packages-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .packages-page .badge-soft {
        border-radius: 999px;
        padding: 0.26rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .packages-page .icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(16, 76, 136, 0.18);
    }

    .packages-page .icon-chip i {
        font-size: 0.9rem;
    }

    .packages-page .icon-chip--blue {
        color: #0f67bf;
        background: linear-gradient(145deg, rgba(55, 153, 246, 0.28), rgba(72, 173, 248, 0.16));
    }

    .packages-page .icon-chip--mint {
        color: #0c907d;
        background: linear-gradient(145deg, rgba(20, 184, 154, 0.26), rgba(111, 222, 203, 0.16));
    }

    .packages-page .icon-chip--violet {
        color: #6d59d8;
        background: linear-gradient(145deg, rgba(129, 96, 255, 0.22), rgba(180, 159, 255, 0.14));
    }

    .packages-page .icon-chip--pink {
        color: #bf4d8a;
        background: linear-gradient(145deg, rgba(242, 113, 188, 0.24), rgba(255, 166, 219, 0.16));
    }

    @media (max-width: 767.98px) {
        .packages-page .card-body {
            padding: 0.9rem;
        }

        .packages-page .form-label,
        .packages-page .small {
            font-size: 0.78rem;
        }

        .packages-page .form-control,
        .packages-page .btn {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 packages-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง packages / customer_packages</div>
            <div>หน้านี้ต้องใช้ตาราง <code>packages</code> และ <code>customer_packages</code></div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <div class="soft-box">
                            <form method="GET" action="{{ route('packages') }}">
                                <label class="form-label small fw-bold">ค้นหาแพ็กเกจ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><span class="icon-chip icon-chip--blue"><i class="bi bi-search"></i></span></span>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อแพ็กเกจ">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="soft-box">
                            <div class="section-title">เพิ่มแพ็กเกจใหม่</div>
                            <form method="POST" action="{{ route('packages.store') }}" class="row g-2">
                                @csrf
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-bold">ชื่อแพ็กเกจ</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น นวดไทย 10 ครั้ง" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold">ราคา (บาท)</label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', '0') }}" required>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small fw-bold">จำนวนสิทธิ์</label>
                                    <input type="number" min="1" name="total_qty" class="form-control" value="{{ old('total_qty', '1') }}" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold">อายุแพ็กเกจ (วัน)</label>
                                    <input type="number" min="1" name="valid_days" class="form-control" value="{{ old('valid_days') }}" placeholder="เว้นว่าง = ไม่หมดอายุ">
                                </div>
                                <div class="col-12 d-grid mt-2">
                                    <button type="submit" class="btn gradient-btn rounded-3">เพิ่มแพ็กเกจ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--pink me-2"><i class="bi bi-box2-heart"></i></span>จัดการแพ็กเกจ</h6>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">ชื่อแพ็กเกจ</th>
                                <th style="min-width: 140px;">ราคา</th>
                                <th style="min-width: 120px;">สิทธิ์รวม</th>
                                <th style="min-width: 140px;">อายุ (วัน)</th>
                                <th class="text-end" style="min-width: 120px;">บันทึก</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $package)
                            <tr>
                                <td>
                                    <form id="package-form-{{ $package['id'] }}" method="POST" action="{{ route('packages.update', ['packageId' => $package['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control" value="{{ $package['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="package-form-{{ $package['id'] }}" type="number" step="0.01" min="0" name="price" class="form-control" value="{{ $package['price'] }}" required>
                                </td>
                                <td>
                                    <input form="package-form-{{ $package['id'] }}" type="number" min="1" name="total_qty" class="form-control" value="{{ $package['total_qty'] }}" required>
                                </td>
                                <td>
                                    <input form="package-form-{{ $package['id'] }}" type="number" min="1" name="valid_days" class="form-control" value="{{ $package['valid_days'] }}" placeholder="ไม่หมดอายุ">
                                </td>
                                <td class="text-end">
                                    <button form="package-form-{{ $package['id'] }}" type="submit" class="btn btn-outline-primary rounded-pill px-3">อัปเดต</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">ยังไม่มีแพ็กเกจ</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm table-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--mint me-2"><i class="bi bi-wallet2"></i></span>ยอดคงเหลือล่าสุด</h6>
            </div>
            <div class="card-body p-2 p-lg-3" style="max-height: 520px; overflow: auto;">
                <div class="d-flex flex-column gap-2">
                    @forelse($balances as $balance)
                    <div class="soft-box p-2">
                        <div class="fw-bold small mb-1">{{ $balance['customer_name'] }}</div>
                        <div class="small text-muted mb-1">{{ $balance['package_name'] }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge-soft">คงเหลือ {{ number_format($balance['remaining_qty']) }} สิทธิ์</span>
                            <small class="text-muted">หมดอายุ: {{ $balance['expired_at'] ?? '-' }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">ยังไม่มียอดคงเหลือ</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <h6 class="fw-bold mb-0"><span class="icon-chip icon-chip--violet me-2"><i class="bi bi-clock-history"></i></span>ประวัติการตัดยอดแพ็กเกจ</h6>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 130px;">เลขที่บิล</th>
                                <th style="min-width: 170px;">วันเวลา</th>
                                <th style="min-width: 180px;">ลูกค้า</th>
                                <th style="min-width: 210px;">แพ็กเกจ</th>
                                <th style="min-width: 120px;">ตัดยอด</th>
                                <th style="min-width: 160px;">ผู้กดตัดยอด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redemptions as $row)
                            <tr>
                                <td>{{ $row['order_no'] }}</td>
                                <td>{{ $row['created_at'] }}</td>
                                <td>{{ $row['customer_name'] }}</td>
                                <td>{{ $row['package_name'] }}</td>
                                <td>{{ number_format($row['qty']) }}</td>
                                <td>{{ $row['redeemed_by'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">ยังไม่มีประวัติการตัดยอด</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
