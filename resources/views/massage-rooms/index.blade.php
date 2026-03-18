@extends('layouts.main')

@section('title', 'ห้องนวด - PlayFlow')
@section('page_title', 'จัดการห้องนวด')

@php
    $currentBranchId = isset($activeBranchId) ? (int) $activeBranchId : null;
@endphp

@push('head')
<style>
    .massage-rooms-index .card { border-radius: 1.15rem; }
    .massage-rooms-index .soft-card {
        border: 1px solid rgba(31, 115, 224, 0.14) !important;
        box-shadow: 0 14px 30px rgba(17, 81, 146, 0.08) !important;
    }
    .massage-rooms-index .section-title {
        color: #1d5d97;
        font-size: 1.02rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .massage-rooms-index .section-hint {
        color: #63809b;
        font-size: 0.82rem;
        margin-bottom: 0;
    }
    .massage-rooms-index .gradient-btn {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.22);
    }
    .massage-rooms-index .gradient-btn:hover { filter: brightness(0.97); }
    .massage-rooms-index .room-action-btn {
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
    .massage-rooms-index .room-action-btn i {
        font-size: 0.95rem;
        line-height: 1;
    }
    .massage-rooms-index .room-action-btn--add {
        background: linear-gradient(135deg, #2d8ff0, #14b89a) !important;
        border-color: #2d8ff0 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 18px rgba(21, 101, 181, 0.22);
    }
    .massage-rooms-index .room-action-btn--add:hover { filter: brightness(0.97); }
    .massage-rooms-index .room-action-btn--edit {
        color: #1f73e0;
        border-color: #2d8ff0;
        background: #ffffff;
    }
    .massage-rooms-index .room-action-btn--delete {
        color: #ef4444;
        border-color: #ff6b7a;
        background: #ffffff;
    }
    .massage-rooms-index .table thead th {
        color: #1d5d97;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
    }
    .massage-rooms-index .table td { vertical-align: middle; }
    .massage-rooms-index .mobile-room-card {
        border: 1px solid rgba(31, 115, 224, 0.14);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 8px 18px rgba(14, 68, 126, 0.05);
    }
    .massage-rooms-index .mobile-room-row {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 0;
    }
    .massage-rooms-index .mobile-room-name {
        flex: 1 1 auto;
        min-width: 0;
        font-weight: 700;
        font-size: 0.95rem;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .massage-rooms-index .room-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(31, 115, 224, 0.1);
        color: #1e62aa;
        font-size: 0.78rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .massage-rooms-index .mobile-room-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-shrink: 0;
    }
    .massage-rooms-index .mobile-room-actions form {
        margin: 0;
        flex-shrink: 0;
    }
    .massage-rooms-index .mobile-room-actions .room-action-btn {
        min-height: 1.95rem;
        padding: 0.28rem 0.62rem;
        font-size: 0.76rem;
        white-space: nowrap;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="row g-3 massage-rooms-index">
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
            <div class="fw-bold mb-1">ยังไม่พบตาราง rooms / beds</div>
            <div>หน้าจัดการห้องนวดต้องใช้ตาราง <code>rooms</code> และ <code>beds</code></div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="card border-0 soft-card">
            <div class="card-body p-3">
                <div class="section-title">เพิ่มห้องนวดใหม่</div>
                <p class="section-hint">เมื่อเพิ่มห้อง ระบบจะสร้างเตียง 1 ให้อัตโนมัติทันที</p>
                <form method="POST" action="{{ route('massage-rooms.rooms.store') }}" class="row g-2 mt-1">
                    @csrf
                    @if($currentBranchId)
                    <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                    @endif
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-bold">ชื่อห้องนวด</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น ห้องนวดไทย 1" required>
                    </div>
                    <div class="col-12 col-md-4 d-grid">
                        <label class="form-label small fw-bold d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn room-action-btn room-action-btn--add">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มห้องนวด
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 d-none d-md-block">
        <div class="card border-0 soft-card">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-door-open me-2 text-primary"></i>รายการห้องนวด</h6>
            </div>
            <div class="card-body p-2 p-lg-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">ชื่อห้องนวด</th>
                                <th style="min-width: 140px;">จำนวนเตียง</th>
                                <th class="text-end" style="min-width: 220px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $room)
                            <tr>
                                <td>{{ $room['name'] }}</td>
                                <td>{{ number_format($room['bed_count'] ?? 0) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('massage-rooms.rooms.edit', array_filter(['roomId' => $room['id'], 'branch_id' => $currentBranchId])) }}" class="btn room-action-btn room-action-btn--edit me-2">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </a>
                                    <form method="POST" action="{{ route('massage-rooms.rooms.destroy', ['roomId' => $room['id']]) }}" class="d-inline" onsubmit="return confirm('ต้องการลบห้องนี้หรือไม่? หากยังมีเตียงอยู่ ระบบจะไม่อนุญาตให้ลบ');">
                                        @csrf
                                        @method('DELETE')
                                        @if($currentBranchId)
                                        <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                                        @endif
                                        <button type="submit" class="btn room-action-btn room-action-btn--delete">
                                            <i class="bi bi-trash3"></i> ลบ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">ยังไม่มีห้องนวด</td>
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
            @forelse($rooms as $room)
            <div class="mobile-room-card p-3">
                <div class="mobile-room-row">
                    <div class="mobile-room-name">{{ $room['name'] }}</div>
                    <span class="room-count-badge"><i class="bi bi-grid-3x3-gap-fill"></i> {{ number_format($room['bed_count'] ?? 0) }} เตียง</span>
                    <div class="mobile-room-actions">
                        <a href="{{ route('massage-rooms.rooms.edit', array_filter(['roomId' => $room['id'], 'branch_id' => $currentBranchId])) }}" class="btn room-action-btn room-action-btn--edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" action="{{ route('massage-rooms.rooms.destroy', ['roomId' => $room['id']]) }}" onsubmit="return confirm('ต้องการลบห้องนี้หรือไม่? หากยังมีเตียงอยู่ ระบบจะไม่อนุญาตให้ลบ');">
                            @csrf
                            @method('DELETE')
                            @if($currentBranchId)
                            <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                            @endif
                            <button type="submit" class="btn room-action-btn btn-outline-danger">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">ยังไม่มีห้องนวด</div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
