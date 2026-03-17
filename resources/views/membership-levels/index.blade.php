@extends('layouts.main')

@section('title', 'ระดับสมาชิก - PlayFlow')
@section('page_title', 'ระดับสมาชิก')

@push('head')
<style>
    .membership-page .card {
        border-radius: 1.1rem;
    }

    .membership-page .hero-card {
        border: 1px solid rgba(31, 115, 224, 0.16) !important;
        background: linear-gradient(168deg, rgba(237, 250, 255, 0.95), rgba(233, 247, 252, 0.9));
        box-shadow: 0 16px 32px rgba(17, 81, 146, 0.09) !important;
        overflow: hidden;
    }

    .membership-page .section-title {
        font-size: 1.08rem;
        font-weight: 700;
        color: #1f5f9a;
        margin-bottom: 0.6rem;
    }

    .membership-page .hint {
        font-size: 0.82rem;
        color: #527593;
    }

    .membership-page .search-shell {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 0.95rem;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(17, 88, 160, 0.06);
        padding: 0.9rem;
        height: 100%;
    }

    .membership-page .create-shell {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 0.95rem;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(17, 88, 160, 0.06);
        padding: 0.95rem;
    }

    .membership-page .search-shell .input-group-text {
        background: #ffffff !important;
        border-right: 0;
    }

    .membership-page .search-shell .form-control {
        border-left: 0;
    }

    .membership-page .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.24);
    }

    .membership-page .gradient-btn:hover {
        filter: brightness(0.96);
    }

    .membership-page .table-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
        overflow: hidden;
    }

    .membership-page .table-head {
        background: linear-gradient(125deg, rgba(45, 143, 240, 0.12), rgba(20, 184, 154, 0.1)) !important;
        border-bottom: 1px solid rgba(31, 115, 224, 0.14) !important;
    }

    .membership-page .tier-table-wrap {
        border: 1px solid rgba(31, 115, 224, 0.14);
        border-radius: 0.95rem;
        overflow: hidden;
        background: #ffffff;
    }

    .membership-page .tier-table th {
        font-size: 0.93rem;
        color: #1f5f9a;
        border-bottom-color: rgba(31, 115, 224, 0.15);
        background: linear-gradient(180deg, #eef6ff 0%, #e9f4ff 100%);
    }

    .membership-page .tier-table td {
        vertical-align: middle;
        border-color: rgba(31, 115, 224, 0.11);
    }

    .membership-page .tier-table tbody tr:hover td {
        background: rgba(31, 115, 224, 0.05);
    }

    .membership-page .tier-mobile-card {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 0.95rem;
        background: linear-gradient(170deg, #ffffff 0%, #f6fbff 100%);
        box-shadow: 0 8px 18px rgba(17, 88, 160, 0.08);
    }

    .membership-page .tier-mobile-header {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.86rem;
        font-weight: 700;
        color: #1f5f9a;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: rgba(45, 143, 240, 0.12);
    }

    .membership-page .tier-mobile-header::before {
        content: "";
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
    }

    @media (max-width: 767.98px) {
        .membership-page .card-body {
            padding: 0.9rem;
        }

        .membership-page .section-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .membership-page .hint {
            font-size: 0.76rem;
        }

        .membership-page .form-label {
            font-size: 0.78rem;
        }

        .membership-page .form-control,
        .membership-page .form-select,
        .membership-page .btn {
            font-size: 0.9rem;
        }

        .membership-page .search-shell,
        .membership-page .create-shell {
            padding: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3 membership-page">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง membership_tiers</div>
            <div>หน้านี้ต้องใช้ตาราง <code>membership_tiers</code></div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="card border-0 shadow-sm hero-card">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <div class="search-shell">
                            <form method="GET" action="{{ route('membership-levels') }}">
                                <label class="form-label small fw-bold">ค้นหาระดับสมาชิก</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อระดับสมาชิก">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div class="create-shell">
                            <div class="section-title">เพิ่มระดับสมาชิกใหม่</div>
                            <form method="POST" action="{{ route('membership-levels.store') }}" class="row g-2">
                                @csrf
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-bold">ชื่อระดับสมาชิก</label>
                                    <input type="text" name="name" class="form-control" placeholder="เช่น ซิลเวอร์" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold">ส่วนลด (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ old('discount_percent', '0') }}" inputmode="decimal" required>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-bold">ยอดใช้จ่ายขั้นต่ำ (บาท)</label>
                                    <input type="number" step="0.01" min="0" name="min_spend" class="form-control" value="{{ old('min_spend', '0') }}" inputmode="decimal" required>
                                </div>
                                <div class="col-12 d-grid mt-2">
                                    <button type="submit" class="btn gradient-btn rounded-3">เพิ่มระดับสมาชิก</button>
                                </div>
                            </form>
                            <div class="hint mt-2">กำหนดจากยอดใช้จ่ายสะสม ลูกค้าจะถูกอัปเดตระดับอัตโนมัติตามเงื่อนไข</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 table-head">
                <h6 class="fw-bold mb-0"><i class="bi bi-sliders me-2 text-primary"></i>จัดการระดับสมาชิก</h6>
            </div>

            <div class="card-body p-2 p-lg-3 d-none d-md-block">
                <div class="table-responsive tier-table-wrap">
                    <table class="table table-hover align-middle mb-0 tier-table">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3" style="min-width: 220px;">ชื่อระดับ</th>
                                <th style="min-width: 170px;">ส่วนลด (%)</th>
                                <th style="min-width: 200px;">ยอดใช้จ่ายขั้นต่ำ (บาท)</th>
                                <th class="text-end" style="min-width: 130px;">บันทึก</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tiers as $tier)
                            <tr>
                                <td class="px-3">
                                    <form id="tier-form-{{ $tier['id'] }}" method="POST" action="{{ route('membership-levels.update', ['tierId' => $tier['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control" value="{{ $tier['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="tier-form-{{ $tier['id'] }}" type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ $tier['discount_percent'] }}" inputmode="decimal" required>
                                </td>
                                <td>
                                    <input form="tier-form-{{ $tier['id'] }}" type="number" step="0.01" min="0" name="min_spend" class="form-control" value="{{ $tier['min_spend'] }}" inputmode="decimal" required>
                                </td>
                                <td class="text-end pe-3">
                                    <button form="tier-form-{{ $tier['id'] }}" type="submit" class="btn btn-outline-primary rounded-pill px-3">อัปเดต</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">ไม่พบระดับสมาชิก</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body p-3 d-md-none">
                <div class="d-flex flex-column gap-2">
                    @forelse($tiers as $tier)
                    <form method="POST" action="{{ route('membership-levels.update', ['tierId' => $tier['id']]) }}" class="tier-mobile-card p-3">
                        @csrf
                        @method('PUT')

                        <div class="tier-mobile-header mb-2">ระดับ #{{ $tier['id'] }}</div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">ชื่อระดับ</label>
                            <input type="text" name="name" class="form-control" value="{{ $tier['name'] }}" required>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">ส่วนลด (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ $tier['discount_percent'] }}" inputmode="decimal" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">ยอดขั้นต่ำ</label>
                                <input type="number" step="0.01" min="0" name="min_spend" class="form-control" value="{{ $tier['min_spend'] }}" inputmode="decimal" required>
                            </div>
                        </div>

                        <button type="submit" class="btn gradient-btn rounded-pill w-100 mt-3">บันทึกการแก้ไข</button>
                    </form>
                    @empty
                    <div class="text-center text-muted py-4">ไม่พบระดับสมาชิก</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
