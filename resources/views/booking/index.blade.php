@extends('layouts.main')

@section('title', 'ตารางคิวนวด - PlayFlow')
@section('page_title', 'ตารางคิวนวด (Interactive)')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex gap-2">
                <input type="date" id="queue-date" class="form-control rounded-pill px-3 shadow-none border-secondary-subtle" value="{{ date('Y-m-d') }}">
                <button class="btn btn-primary rounded-pill px-4" onclick="openModal()"><i class="bi bi-plus-lg me-2"></i> เพิ่มคิว</button>
            </div>
            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-info-circle me-1"></i> คลิกที่คิวเพื่อเปลี่ยนสถานะ
            </span>
        </div>

        <div class="table-responsive rounded-4 border overflow-hidden">
            <div id="queue-board" class="d-grid bg-white" 
                 style="min-width: 900px; --staff-count: {{ count($staff) }}; --slot-height: 80px; grid-template-columns: 80px repeat(var(--staff-count), 1fr);">
                
                <div class="bg-light p-3 text-center fw-bold border-end border-bottom">เวลา</div>
                @foreach($staff as $s)
                <div class="bg-light p-3 text-center border-end border-bottom">
                    <div class="fw-bold">{{ $s['name'] }}</div>
                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $s['role'] }}</div>
                </div>
                @endforeach

                <div class="bg-light border-end">
                    @for($h = 10; $h <= 20; $h++)
                    <div class="d-flex align-items-center justify-content-center border-bottom fw-bold text-muted small" style="height: var(--slot-height);">{{ $h }}:00</div>
                    @endfor
                </div>

                @foreach($staff as $s)
                <div class="position-relative border-end" data-staff-id="{{ $s['id'] }}">
                    @for($h = 10; $h <= 20; $h++)
                    <div class="border-bottom" style="height: var(--slot-height); cursor: crosshair;" onclick="openModal({staffId:'{{$s['id']}}', time:'{{$h}}:00'})"></div>
                    @endfor
                    <div class="booking-layer position-absolute top-0 start-0 w-100 h-100" id="layer-{{ $s['id'] }}" style="pointer-events: none;"></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@include('booking.partials.modal')
@endsection

@push('head')
<style>
    .booking-card { 
        position: absolute; left: 5px; right: 5px; border-radius: 12px; border: 1px solid; 
        padding: 6px 10px; font-size: 0.75rem; cursor: pointer; pointer-events: auto; z-index: 50;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .booking-card:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .state-waiting { background: #e3f2fd; border-color: #90caf9; color: #1976d2; }
    .state-in_service { background: #e8f5e9; border-color: #a5d6a7; color: #2e7d32; }
    .state-completed { background: #f5f5f5; border-color: #bdbdbd; color: #616161; opacity: 0.7; }
    .state-cancelled { background: #ffebee; border-color: #ef9a9a; color: #c62828; text-decoration: line-through; }
</style>
@endpush

@push('scripts')
<script>
    const staffData = @json($staff);
    const statusKeys = Object.keys(@json($statuses));
    let bookings = [];

    // Populate Initial Bookings
    staffData.forEach(s => s.queue.forEach(q => {
        bookings.push({
            id: q.booking_id, customer: q.customer, service: q.service,
            staffId: s.id, start: q.start, end: q.end, status: q.status
        });
    }));

    function renderBookings() {
        document.querySelectorAll('.booking-layer').forEach(l => l.innerHTML = '');
        const slotH = 80;
        const startH = 10;

        bookings.forEach(b => {
            const layer = document.getElementById(`layer-${b.staffId}`);
            if (!layer) return;

            const startM = (parseInt(b.start.split(':')[0]) - startH) * 60 + parseInt(b.start.split(':')[1]);
            const endM = (parseInt(b.end.split(':')[0]) - startH) * 60 + parseInt(b.end.split(':')[1]);
            
            const card = document.createElement('div');
            card.className = `booking-card state-${b.status} shadow-sm`;
            card.style.top = `${(startM/60) * slotH + 2}px`;
            card.style.height = `${((endM - startM)/60) * slotH - 4}px`;
            card.innerHTML = `<div class="fw-bold mb-1">${b.customer}</div><div class="opacity-75">${b.service}</div>`;
            
            card.onclick = (e) => {
                e.stopPropagation();
                const currentIdx = statusKeys.indexOf(b.status);
                b.status = statusKeys[(currentIdx + 1) % statusKeys.length];
                renderBookings();
            };
            layer.appendChild(card);
        });
    }

    function openModal(data = {}) {
        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        if(data.staffId) document.getElementById('staff-select').value = data.staffId;
        if(data.time) document.getElementById('start-time').value = data.time.padStart(5, '0');
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', renderBookings);
</script>
@endpush