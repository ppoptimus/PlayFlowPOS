@extends('layouts.main')

@section('title', 'Membership Levels - PlayFlow')
@section('page_title', 'Membership Levels')

@section('content')
<div class="row g-3">
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
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-6">
                        <form method="GET" action="{{ route('membership-levels') }}">
                            <label class="form-label small fw-bold">ค้นหาระดับสมาชิก</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อระดับสมาชิก">
                            </div>
                        </form>
                    </div>
                    <div class="col-12 col-lg-6">
                        <form method="POST" action="{{ route('membership-levels.store') }}" class="row g-2">
                            @csrf
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold">Tier Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Silver" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold">Discount %</label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="0" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold">Min Spend</label>
                                <input type="number" step="0.01" min="0" name="min_spend" class="form-control" value="0" required>
                            </div>
                            <div class="col-12 col-md-2 d-grid">
                                <label class="form-label small fw-bold d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Manage Tiers</h6>
            </div>
            <div class="card-body p-0 p-lg-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Tier</th>
                                <th>Discount %</th>
                                <th>Min Spend</th>
                                <th class="text-end">Save</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tiers as $tier)
                            <tr>
                                <td class="px-3">
                                    <form id="tier-form-{{ $tier['id'] }}" method="POST" action="{{ route('membership-levels.update', ['tierId' => $tier['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $tier['name'] }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="tier-form-{{ $tier['id'] }}" type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control form-control-sm" value="{{ $tier['discount_percent'] }}" required>
                                </td>
                                <td>
                                    <input form="tier-form-{{ $tier['id'] }}" type="number" step="0.01" min="0" name="min_spend" class="form-control form-control-sm" value="{{ $tier['min_spend'] }}" required>
                                </td>
                                <td class="text-end pe-3">
                                    <button form="tier-form-{{ $tier['id'] }}" type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Update</button>
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
        </div>
    </div>
    @endif
</div>
@endsection
