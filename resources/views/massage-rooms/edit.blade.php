@extends('layouts.main')

@section('title', 'แก้ไขห้องนวด - PlayFlow')
@section('page_title', 'แก้ไขห้องนวด')

@php
    $currentBranchId = isset($activeBranchId) ? (int) $activeBranchId : null;
    $beds = $room['beds'] ?? [];
@endphp

@push('head')
<style>
    .massage-room-edit .card { border-radius: 1.15rem; }
    .massage-room-edit .soft-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }
    .massage-room-edit .section-title {
        color: #1d5d97;
        font-size: 1.02rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .massage-room-edit .section-hint {
        color: #63809b;
        font-size: 0.82rem;
        margin-bottom: 0;
    }
    .massage-room-edit .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.22);
    }
    .massage-room-edit .gradient-btn:hover { filter: brightness(0.97); }
    .massage-room-edit .room-action-btn {
        min-height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.4rem 0.95rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1;
    }
    .massage-room-edit .room-action-btn i {
        font-size: 0.95rem;
        line-height: 1;
    }
    .massage-room-edit .room-action-btn--add,
    .massage-room-edit .room-action-btn--save {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.22);
    }
    .massage-room-edit .room-action-btn--add:hover,
    .massage-room-edit .room-action-btn--save:hover { filter: brightness(0.97); }
    .massage-room-edit .room-action-btn--delete {
        color: #ef4444;
        border-color: #ff6b7a;
        background: #ffffff;
    }
    .massage-room-edit .table thead th {
        color: #1d5d97;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
    }
    .massage-room-edit .table td { vertical-align: middle; }
    .massage-room-edit .mobile-bed-card {
        border: 1px solid rgba(31, 115, 224, 0.14);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 8px 18px rgba(14, 68, 126, 0.05);
    }
</style>
@endpush

@section('content')
<div class="row g-3 massage-room-edit">
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

    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('massage-rooms', array_filter(['branch_id' => $currentBranchId])) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> กลับรายการห้องนวด
            </a>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 soft-card h-100">
            <div class="card-body p-3">
                <div class="section-title">แก้ไขชื่อห้องนวด</div>
                <p class="section-hint">แก้ชื่อห้องนี้ได้จากฟอร์มด้านล่าง</p>
                <form method="POST" action="{{ route('massage-rooms.rooms.update', ['roomId' => $room['id']]) }}" class="row g-2 mt-1">
                    @csrf
                    @method('PUT')
                    @if($currentBranchId)
                    <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                    @endif
                    <div class="col-12">
                        <label class="form-label small fw-bold">ชื่อห้องนวด</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $room['name']) }}" required>
                    </div>
                    <div class="col-12 d-grid mt-2">
                        <button type="submit" class="btn room-action-btn room-action-btn--save">
                            <i class="bi bi-save"></i> บันทึกชื่อห้อง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 soft-card h-100">
            <div class="card-body p-3">
                <div class="section-title">เพิ่มเตียง</div>
                <p class="section-hint">เพิ่มเตียงใหม่เข้าในห้อง {{ $room['name'] }}</p>
                <form method="POST" action="{{ route('massage-rooms.beds.store') }}" class="row g-2 mt-1">
                    @csrf
                    @if($currentBranchId)
                    <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                    @endif
                    <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                    <input type="hidden" name="status" value="available">
                    <div class="col-12">
                        <label class="form-label small fw-bold">ชื่อเตียง</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น เตียง A" required>
                    </div>
                    <div class="col-12 d-grid mt-2">
                        <button type="submit" class="btn room-action-btn room-action-btn--add">
                            <i class="bi bi-plus-circle"></i> เพิ่มเตียง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 d-none d-md-block">
        <div class="card border-0 soft-card">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>รายการเตียง</h6>
                <span class="small text-muted">{{ number_format(count($beds)) }} เตียง</span>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 90px;">#</th>
                                <th style="min-width: 220px;">ชื่อเตียง</th>
                                <th class="text-end" style="min-width: 160px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beds as $index => $bed)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bed['name'] }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('massage-rooms.beds.destroy', ['bedId' => $bed['id']]) }}" class="d-inline" onsubmit="return confirm('ต้องการลบเตียงนี้หรือไม่?');">
                                        @csrf
                                        @method('DELETE')
                                        @if($currentBranchId)
                                        <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                                        @endif
                                        <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                                        <button type="submit" class="btn room-action-btn room-action-btn--delete">
                                            <i class="bi bi-trash3"></i> ลบเตียง
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">ห้องนี้ยังไม่มีเตียง</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-md-none">
        <div class="d-flex flex-column gap-2">
            @forelse($beds as $index => $bed)
            <div class="mobile-bed-card p-3">
                <div class="small text-muted">เตียง {{ $index + 1 }}</div>
                <div class="fw-bold mt-1">{{ $bed['name'] }}</div>
                <form method="POST" action="{{ route('massage-rooms.beds.destroy', ['bedId' => $bed['id']]) }}" class="mt-3" onsubmit="return confirm('ต้องการลบเตียงนี้หรือไม่?');">
                    @csrf
                    @method('DELETE')
                    @if($currentBranchId)
                    <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                    @endif
                    <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                    <button type="submit" class="btn room-action-btn room-action-btn--delete w-100">
                        <i class="bi bi-trash3"></i> ลบเตียง
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center text-muted py-4">ห้องนี้ยังไม่มีเตียง</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
