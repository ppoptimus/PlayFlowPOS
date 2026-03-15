@extends('layouts.main')

@section('title', 'Booking | PlayFlow Spa POS')
@section('page_title', 'ตารางคิวนวด')
@section('page_subtitle', 'แตะช่องว่างเพื่อเพิ่มคิว / แตะที่คิวเพื่อเปลี่ยนสถานะ')

@section('content')
<section class="pf-card booking-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="pf-section-title mb-0">Queue Calendar (Mock)</h3>
            <div class="small text-secondary">โหมดจำลองไม่เชื่อม MySQL</div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <input type="date" id="queue-date" class="form-control form-control-sm" style="max-width: 180px;">
            <button class="btn btn-sm text-white fw-semibold" id="quick-add-btn" style="background:linear-gradient(120deg,#31b8e9,#1cc9b6); border:none;">
                + เพิ่มคิว
            </button>
        </div>
    </div>

    <div class="queue-board-wrap">
        <div class="queue-board" id="queue-board"
             style="--staff-count: {{ count($staff) }}; --slot-height: 86px;">
            <div class="time-head">เวลา</div>
            @foreach($staff as $s)
            <div class="staff-head">
                <div class="staff-badge">{{ mb_substr($s['name'], 0, 1) }}</div>
                <div class="staff-name">{{ $s['name'] }}</div>
                <div class="staff-role">{{ $s['role'] }}</div>
            </div>
            @endforeach

            <div class="time-col">
                @for($hour = 10; $hour <= 20; $hour++)
                <div class="time-slot">{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}:00</div>
                @endfor
            </div>

            @foreach($staff as $s)
            <div class="staff-col" data-staff-id="{{ $s['id'] }}">
                <div class="staff-slots">
                    @for($hour = 10; $hour <= 20; $hour++)
                    <button type="button"
                            class="slot-btn"
                            data-staff-id="{{ $s['id'] }}"
                            data-time="{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}:00"
                            title="เพิ่มคิว"></button>
                    @endfor
                    <div class="booking-layer" data-layer-for="{{ $s['id'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">เพิ่มคิวใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="booking-form" class="row g-2">
                    <div class="col-12">
                        <label class="small text-secondary fw-semibold mb-1">เบอร์โทรค้นหาลูกค้าเก่า</label>
                        <input id="phone-search" class="form-control" placeholder="เช่น 089-111-1111">
                        <div id="customer-hint" class="small mt-1 text-secondary">ค้นหาจาก mock data</div>
                    </div>
                    <div class="col-12">
                        <label class="small text-secondary fw-semibold mb-1">ลูกค้า</label>
                        <select id="customer-select" class="form-select" required>
                            <option value="">เลือกลูกค้า</option>
                            @foreach($customers as $c)
                            <option value="{{ $c['id'] }}" data-phone="{{ $c['phone'] }}">{{ $c['name'] }} ({{ $c['phone'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="small text-secondary fw-semibold mb-1">บริการ</label>
                        <select id="service-select" class="form-select" required>
                            <option value="">เลือกบริการ</option>
                            @foreach($serviceItems as $item)
                            <option value="{{ $item['id'] }}" data-duration="{{ $item['duration'] }}">{{ $item['name'] }} ({{ $item['duration'] }} นาที)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="small text-secondary fw-semibold mb-1">วันที่</label>
                        <input id="booking-date" type="date" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="small text-secondary fw-semibold mb-1">เวลาเริ่ม</label>
                        <input id="start-time" type="time" class="form-control" min="10:00" max="20:00" step="900" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="small text-secondary fw-semibold mb-1">หมอนวด</label>
                        <select id="staff-select" class="form-select" required>
                            <option value="">เลือกหมอนวด</option>
                            @foreach($staff as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="small text-secondary fw-semibold mb-1">ห้อง/เตียง</label>
                        <select id="room-select" class="form-select" required>
                            <option value="">เลือกห้อง</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room['id'] }}">{{ $room['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="save-booking-btn" class="btn text-white rounded-pill px-3" style="background:linear-gradient(120deg,#31b8e9,#1cc9b6); border:none;">
                    บันทึกคิว
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .booking-shell {
        overflow: hidden;
    }

    .queue-board-wrap {
        overflow-x: auto;
        border-radius: 16px;
        border: 1px solid rgba(45, 122, 170, 0.2);
    }

    .queue-board {
        min-width: 880px;
        display: grid;
        grid-template-columns: 78px repeat(var(--staff-count), minmax(220px, 1fr));
        background: #f8fbfd;
    }

    .time-head,
    .staff-head {
        position: sticky;
        top: 0;
        z-index: 4;
        background: #f3f7fa;
        border-bottom: 1px solid #d8e3ec;
        min-height: 66px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #2c4d6d;
    }

    .time-head {
        font-weight: 700;
        border-right: 1px solid #d8e3ec;
        font-size: 0.9rem;
    }

    .staff-head {
        border-right: 1px solid #d8e3ec;
        flex-direction: column;
        gap: 2px;
        padding: 6px;
    }

    .staff-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #245c56;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.76rem;
    }

    .staff-name {
        font-weight: 700;
        font-size: 0.92rem;
        line-height: 1;
    }

    .staff-role {
        font-size: 0.7rem;
        color: #6a8297;
        line-height: 1;
    }

    .time-col,
    .staff-col {
        border-right: 1px solid #d8e3ec;
    }

    .time-slot {
        height: var(--slot-height);
        border-bottom: 1px solid #d8e3ec;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 10px;
        color: #385877;
        font-weight: 700;
        font-size: 0.88rem;
        background: #f8fbfd;
    }

    .staff-slots {
        position: relative;
    }

    .slot-btn {
        display: block;
        width: 100%;
        height: var(--slot-height);
        border: 0;
        border-bottom: 1px solid #d8e3ec;
        background: transparent;
        transition: background 0.15s ease;
    }

    .slot-btn:hover {
        background: rgba(49, 184, 233, 0.08);
    }

    .booking-layer {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .booking-card {
        position: absolute;
        left: 6px;
        right: 6px;
        border-radius: 12px;
        border: 1px solid;
        padding: 7px 8px;
        box-shadow: 0 3px 10px rgba(26, 83, 131, 0.14);
        pointer-events: auto;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow: hidden;
        z-index: 2;
    }

    .booking-card .title {
        font-weight: 700;
        font-size: 0.88rem;
        color: #203f60;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .booking-card .service {
        font-size: 0.72rem;
        color: #44667f;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .booking-card .meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        gap: 8px;
        white-space: nowrap;
        margin-top: auto;
    }

    .booking-card.compact .title {
        font-size: 0.82rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .booking-card.mid {
        padding: 6px 8px;
        gap: 2px;
    }

    .booking-card.mid .title {
        font-size: 0.84rem;
    }

    .booking-card.mid .service {
        font-size: 0.7rem;
        line-height: 1.05;
    }

    .booking-card.mid .meta {
        font-size: 0.68rem;
        line-height: 1;
    }

    .booking-card.compact {
        padding: 4px 7px;
        gap: 1px;
    }

    .booking-card.compact .service {
        font-size: 0.68rem;
        line-height: 1.05;
    }

    .booking-card.compact .meta {
        font-size: 0.66rem;
        line-height: 1;
    }

    .booking-card.ultra-compact .title {
        font-size: 0.78rem;
    }

    .booking-card.ultra-compact .service {
        display: none;
    }

    .booking-card.ultra-compact .meta .status {
        display: none;
    }

    .state-waiting {
        background: #e6f3ff;
        border-color: #99c9ff;
    }
    .state-waiting .meta { color: #2f6db0; }

    .state-in_service {
        background: #dff5ec;
        border-color: #8fd5b7;
    }
    .state-in_service .meta { color: #1b8964; }

    .state-completed {
        background: #edf2ef;
        border-color: #b7c8be;
    }
    .state-completed .meta { color: #5f7468; }

    .state-cancelled {
        background: #ffeaea;
        border-color: #f2b4b4;
    }
    .state-cancelled .meta { color: #b44f4f; }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const staff = @json($staff);
        const rooms = @json($rooms);
        const customers = @json($customers);
        const services = @json($serviceItems);
        const statusLabels = @json($statuses);
        const statusCycle = ["waiting", "in_service", "completed", "cancelled"];
        const slotHeight = document.querySelector(".slot-btn")?.getBoundingClientRect().height || 86;
        const dayStartHour = 10;
        const dayEndHour = 20;

        const queueDateInput = document.getElementById("queue-date");
        const bookingDate = document.getElementById("booking-date");
        const bookingForm = document.getElementById("booking-form");
        const customerSelect = document.getElementById("customer-select");
        const serviceSelect = document.getElementById("service-select");
        const staffSelect = document.getElementById("staff-select");
        const roomSelect = document.getElementById("room-select");
        const startTime = document.getElementById("start-time");
        const phoneSearch = document.getElementById("phone-search");
        const customerHint = document.getElementById("customer-hint");
        const quickAddBtn = document.getElementById("quick-add-btn");
        const saveBookingBtn = document.getElementById("save-booking-btn");

        const modalEl = document.getElementById("bookingModal");
        const bookingModal = new bootstrap.Modal(modalEl);

        function toMinutes(time) {
            const [h, m] = time.split(":").map(Number);
            return (h * 60) + m;
        }

        function fromMinutes(total) {
            const h = String(Math.floor(total / 60)).padStart(2, "0");
            const m = String(total % 60).padStart(2, "0");
            return `${h}:${m}`;
        }

        function addMinutes(start, mins) {
            return fromMinutes(toMinutes(start) + mins);
        }

        function overlap(aStart, aEnd, bStart, bEnd) {
            return toMinutes(aStart) < toMinutes(bEnd) && toMinutes(aEnd) > toMinutes(bStart);
        }

        const today = new Date().toISOString().slice(0, 10);
        queueDateInput.value = today;
        bookingDate.value = today;

        const bookings = [];
        staff.forEach((s) => {
            s.queue.forEach((q) => {
                bookings.push({
                    id: q.booking_id,
                    customer: q.customer,
                    service: q.service,
                    date: today,
                    staffId: s.id,
                    roomId: q.room_id,
                    start: q.start,
                    end: q.end,
                    status: q.status,
                });
            });
        });

        function renderBookings() {
            document.querySelectorAll(".booking-layer").forEach((layer) => {
                layer.innerHTML = "";
            });

            const activeDate = queueDateInput.value;
            const minY = 0;
            const maxMinutes = (dayEndHour - dayStartHour + 1) * 60;

            bookings
                .filter((b) => b.date === activeDate)
                .forEach((b) => {
                    const layer = document.querySelector(`.booking-layer[data-layer-for="${b.staffId}"]`);
                    if (!layer) return;

                    const startMinsFromDay = toMinutes(b.start) - (dayStartHour * 60);
                    const endMinsFromDay = toMinutes(b.end) - (dayStartHour * 60);
                    if (endMinsFromDay <= 0 || startMinsFromDay >= maxMinutes) return;

                    const clampedStart = Math.max(minY, startMinsFromDay);
                    const clampedEnd = Math.min(maxMinutes, endMinsFromDay);
                    if (clampedEnd <= clampedStart) return;

                    const rawTop = (clampedStart / 60) * slotHeight + 3;
                    const scaledHeight = ((clampedEnd - clampedStart) / 60) * slotHeight - 6;
                    const durationMinutes = clampedEnd - clampedStart;
                    const isMid = durationMinutes >= 45 && durationMinutes < 60;
                    const isCompact = durationMinutes < 45;
                    const isUltraCompact = durationMinutes < 30;
                    const minHeight = isUltraCompact ? 30 : (isCompact ? 38 : 52);
                    const height = Math.max(minHeight, scaledHeight);
                    const maxTop = Math.max(minY, (maxMinutes / 60) * slotHeight - height - 3);
                    const top = Math.min(rawTop, maxTop);

                    const customer = customers.find((c) => c.name === b.customer) || null;
                    const room = rooms.find((r) => r.id === b.roomId);

                    const card = document.createElement("button");
                    card.type = "button";
                    card.className = `booking-card state-${b.status}`;
                    card.style.top = `${top}px`;
                    card.style.height = `${height}px`;
                    card.dataset.bookingId = b.id;
                    if (isMid) {
                        card.classList.add("mid");
                    }
                    if (isCompact) {
                        card.classList.add("compact");
                    }
                    if (isUltraCompact) {
                        card.classList.add("ultra-compact");
                    }
                    card.innerHTML = `
                        <div class="title">${b.customer}</div>
                        <div class="service">${b.service}${room ? ` - ${room.name}` : ""}</div>
                        <div class="meta">
                            <span class="time">&#128339; ${b.start}</span>
                            <span class="status">${(statusLabels[b.status] || b.status).toUpperCase()}</span>
                        </div>
                    `;
                    if (customer && customer.phone && customer.phone !== "-") {
                        card.title = `${customer.name} (${customer.phone})`;
                    }

                    card.addEventListener("click", () => {
                        const currentIndex = statusCycle.indexOf(b.status);
                        b.status = statusCycle[(currentIndex + 1) % statusCycle.length];
                        renderBookings();
                    });

                    layer.appendChild(card);
                });
        }

        function openModal(prefill = {}) {
            bookingForm.reset();
            bookingDate.value = queueDateInput.value || today;
            customerHint.textContent = "ค้นหาจาก mock data";
            customerHint.className = "small mt-1 text-secondary";

            if (prefill.staffId) staffSelect.value = prefill.staffId;
            if (prefill.time) startTime.value = prefill.time;

            bookingModal.show();
        }

        function detectConflict(payload) {
            return bookings.find((b) => {
                if (b.date !== payload.date) return false;
                if (b.status === "completed" || b.status === "cancelled") return false;
                const sameStaff = b.staffId === payload.staffId;
                const sameRoom = b.roomId === payload.roomId;
                if (!sameStaff && !sameRoom) return false;
                return overlap(b.start, b.end, payload.start, payload.end);
            });
        }

        function saveBooking() {
            if (!bookingForm.reportValidity()) return;

            const customerOption = customerSelect.options[customerSelect.selectedIndex];
            const serviceOption = serviceSelect.options[serviceSelect.selectedIndex];
            const duration = Number(serviceOption.dataset.duration || 0);
            const end = addMinutes(startTime.value, duration);

            const payload = {
                id: `BK${Date.now().toString().slice(-6)}`,
                customer: customerOption.text.split(" (")[0],
                service: serviceOption.text.split(" (")[0],
                date: bookingDate.value,
                staffId: staffSelect.value,
                roomId: roomSelect.value,
                start: startTime.value,
                end,
                status: "waiting",
            };

            const conflict = detectConflict(payload);
            if (conflict) {
                alert(`เวลาไม่ว่าง: ชนกับคิว ${conflict.id} (${conflict.start}-${conflict.end})`);
                return;
            }

            bookings.push(payload);
            bookingModal.hide();
            renderBookings();
        }

        document.querySelectorAll(".slot-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                openModal({
                    staffId: btn.dataset.staffId,
                    time: btn.dataset.time,
                });
            });
        });

        quickAddBtn.addEventListener("click", () => openModal());
        saveBookingBtn.addEventListener("click", saveBooking);
        queueDateInput.addEventListener("change", () => {
            bookingDate.value = queueDateInput.value;
            renderBookings();
        });

        phoneSearch.addEventListener("input", () => {
            const target = customers.find((c) => c.phone === phoneSearch.value.trim());
            if (!target) {
                customerHint.textContent = "ไม่พบลูกค้าใน mock data";
                customerHint.className = "small mt-1 text-secondary";
                return;
            }
            customerHint.textContent = `พบลูกค้าเดิม: ${target.name}`;
            customerHint.className = "small mt-1 text-success";
            customerSelect.value = target.id;
        });

        modalEl.addEventListener("hidden.bs.modal", () => {
            phoneSearch.value = "";
        });

        renderBookings();
    })();
</script>
@endpush
