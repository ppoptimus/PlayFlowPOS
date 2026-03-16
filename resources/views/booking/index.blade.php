@extends('layouts.main')

@section('title', 'ตารางคิวนวด - PlayFlow')
@section('page_title', 'ตารางคิวนวด')

@section('content')
@php
    $startHour = 10;
    $endHour = 20;
    $slotCount = ($endHour - $startHour) + 1;
@endphp

<div class="booking-page booking-mobile-safe">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4" style="overflow-x:hidden;">
            <div class="queue-toolbar d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                <div class="d-flex flex-wrap gap-2 align-items-center w-100" style="max-width: 100%;">
                    <input type="date" id="queue-date" class="form-control rounded-pill px-3 shadow-none border-secondary-subtle flex-grow-1" value="{{ $selectedDate }}" style="width: auto; max-width: 160px;">
                    <button class="btn btn-primary rounded-pill px-4 flex-shrink-0" onclick="openModal()"><i class="bi bi-plus-lg me-2"></i> เพิ่มคิว</button>
                </div>
                <span class="badge text-bg-light border rounded-3 px-3 py-2 fw-semibold text-wrap text-start lh-base d-block w-100 w-md-auto">
                    <i class="bi bi-info-circle me-1"></i> กดแทบคิวเพื่อแก้บริการ/เวลา/หมอ และชำระเงิน
                </span>
            </div>

            <div class="table-responsive rounded-4 border queue-board-wrap">
                <div id="queue-board" class="queue-board bg-white" style="--slot-count: {{ $slotCount }};">
                    <div class="queue-grid-row queue-head-row">
                        <div class="queue-cell queue-staff-head">หมอนวด</div>
                        @for($h = $startHour; $h <= $endHour; $h++)
                        <div class="queue-cell queue-time-head">{{ sprintf('%02d:00', $h) }}</div>
                        @endfor
                    </div>

                    @foreach($staff as $s)
                    <div class="queue-grid-row queue-data-row" data-staff-id="{{ $s['id'] }}">
                        <div class="queue-cell queue-staff-cell">
                            <div class="fw-bold">{{ $s['name'] }}</div>
                            <div class="text-muted small queue-staff-role">{{ preg_replace('/\s*\([^)]*\)/', '', $s['role']) }}</div>
                        </div>
                        @for($h = $startHour; $h <= $endHour; $h++)
                        <div class="queue-cell queue-slot-cell"
                            data-time="{{ sprintf('%02d:00', $h) }}"
                            onclick="openModal({staffId:'{{ $s['id'] }}', time:'{{ sprintf('%02d:00', $h) }}'})"></div>
                        @endfor
                        <div class="booking-row-layer" id="layer-{{ $s['id'] }}" data-staff-id="{{ $s['id'] }}"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@include('booking.partials.modal')
@endsection

@push('head')
<style>
    .queue-board {
        --staff-col-width: 190px;
        --slot-width: 118px;
        min-width: calc(var(--staff-col-width) + (var(--slot-count) * var(--slot-width)));
    }
    .queue-grid-row {
        display: grid;
        grid-template-columns: var(--staff-col-width) repeat(var(--slot-count), minmax(var(--slot-width), 1fr));
        position: relative;
    }
    .queue-cell {
        border-top: 1px solid #e5edf5;
    }
    .queue-head-row .queue-cell {
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f4f8fc;
        border-top: 0;
        border-bottom: 1px solid #d9e6f2;
        font-weight: 700;
        color: #27496d;
    }
    .queue-staff-head,
    .queue-staff-cell {
        position: sticky;
        left: 0;
        z-index: 30;
        border-right: 1px solid #dce7f2;
        background: #f8fbff;
    }
    .queue-staff-head {
        z-index: 40;
        justify-content: flex-start;
        padding-left: 1rem;
    }
    .queue-time-head {
        border-left: 1px solid #e5edf5;
        font-size: 0.86rem;
    }
    .queue-data-row .queue-cell {
        min-height: 94px;
    }
    .queue-staff-cell {
        padding: 0.9rem 0.85rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.2rem;
    }
    .queue-slot-cell {
        border-left: 1px solid #edf2f7;
        cursor: crosshair;
        background: linear-gradient(180deg, #ffffff 0%, #fcfeff 100%);
        transition: background-color 0.15s;
    }
    .queue-slot-cell:hover {
        background: #edf7ff;
    }
    .booking-row-layer {
        position: absolute;
        left: var(--staff-col-width);
        right: 0;
        top: 0;
        bottom: 0;
        pointer-events: auto;
        z-index: 10;
    }
    .booking-card {
        position: absolute;
        top: 8px;
        height: calc(100% - 16px);
        border-radius: 12px;
        border: 1px solid;
        padding: 0.42rem 0.58rem;
        cursor: pointer;
        pointer-events: auto;
        z-index: 50;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        min-width: 84px;
    }
    .booking-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 66, 120, 0.16);
    }
    .booking-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.68rem;
        margin-bottom: 0.18rem;
    }
    .booking-time {
        font-weight: 700;
        opacity: 0.9;
    }
    .booking-paid,
    .booking-unpaid {
        border-radius: 999px;
        padding: 0.06rem 0.42rem;
        font-size: 0.62rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .booking-paid {
        background: rgba(20, 184, 154, 0.18);
        color: #0f8b73;
    }
    .booking-unpaid {
        background: rgba(31, 115, 224, 0.15);
        color: #1a5ea8;
    }
    .booking-customer {
        font-weight: 700;
        font-size: 0.84rem;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .booking-service {
        opacity: 0.86;
        font-size: 0.72rem;
        line-height: 1.18;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .state-waiting {
        background: #e6f5ff;
        border-color: #9fd5f5;
        color: #245f98;
    }
    .state-in_service {
        background: #e3f9f2;
        border-color: #98e3d1;
        color: #117a67;
    }
    .state-completed {
        background: #eef4f8;
        border-color: #cbdae4;
        color: #4e7186;
    }
    .state-cancelled {
        background: #fff1f1;
        border-color: #f4b7b7;
        color: #c34a4a;
    }
    .selected-service-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.22rem 0.52rem;
        background-color: #ffffff;
        border: 1px solid #b6d5ef;
        color: #275f98;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .selected-service-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        padding: 0;
        line-height: 1;
    }
    @media (max-width: 991.98px) {
        .booking-mobile-safe {
            padding-bottom: 5.4rem;
        }
        .queue-board {
            --staff-col-width: 90px;
            --slot-width: 100px;
        }
        .queue-staff-cell {
            padding: 0.6rem 0.5rem;
        }
        .queue-staff-cell .fw-bold {
            font-size: 0.8rem;
        }
        .queue-staff-role {
            font-size: 0.68rem;
        }
        .queue-staff-head {
            padding-left: 0.5rem;
            font-size: 0.8rem;
        }
        .queue-head-row .queue-cell {
            height: 48px;
        }
        .queue-time-head {
            font-size: 0.75rem;
        }
        .queue-data-row .queue-cell {
            min-height: 78px;
        }
        .booking-card {
            top: 6px;
            height: calc(100% - 12px);
            padding: 0.3rem 0.4rem;
            border-radius: 8px;
            min-width: 70px;
        }
        .booking-customer { font-size: 0.78rem; }
        .booking-service { font-size: 0.66rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    const START_HOUR = {{ $startHour }};
    const END_HOUR_EXCLUSIVE = {{ $endHour + 1 }};
    const activeBranchId = @json($activeBranchId);
    const staffData = @json($staff);
    const customerData = @json($customers);
    const serviceData = @json($serviceItems);
    const bedData = @json($beds);
    const initialBookings = @json($bookings);

    const bookingModalEl = document.getElementById('bookingModal');
    const queueBoardEl = document.getElementById('queue-board');
    const queueDateEl = document.getElementById('queue-date');
    const bookingFormEl = document.getElementById('booking-form');
    const customerPhoneEl = document.getElementById('customer-phone');
    const customerPhoneHintEl = document.getElementById('customer-phone-hint');
    const customerSelectEl = document.getElementById('customer-select');
    const staffSelectEl = document.getElementById('staff-select');
    const bedSelectEl = document.getElementById('bed-select');
    const startTimeEl = document.getElementById('start-time');
    const endTimeEl = document.getElementById('end-time');
    const statusSelectEl = document.getElementById('status-select');
    const serviceSelectEl = document.getElementById('service-select');
    const selectedServicesEl = document.getElementById('selected-services');
    const bookingTotalEl = document.getElementById('booking-total');
    const bookingTitleEl = document.getElementById('booking-modal-title');
    const bookingSubtitleEl = document.getElementById('booking-modal-subtitle');
    const saveBookingBtn = document.getElementById('save-booking-btn');
    const deleteBookingBtn = document.getElementById('delete-booking-btn');
    const addServiceBtn = document.getElementById('add-service-btn');
    const payBookingBtn = document.getElementById('pay-booking-btn');

    const bookingApi = {
        data: "{{ route('booking.data') }}",
        store: "{{ route('booking.store') }}",
        updateBase: "{{ url('/booking') }}",
        pos: "{{ route('pos') }}",
        csrfToken: "{{ csrf_token() }}",
    };

    function normalizeId(value) {
        return value === null || value === undefined ? '' : String(value);
    }

    const customerMap = new Map(customerData.map(c => [normalizeId(c.id), c]));
    const serviceMap = new Map(serviceData.map(s => [normalizeId(s.id), s]));
    const bedMap = new Map(bedData.map(b => [normalizeId(b.id), b]));

    const defaultCustomerId = customerData.length ? normalizeId(customerData[0].id) : '';
    const defaultServiceId = serviceData.length ? normalizeId(serviceData[0].id) : '';
    const defaultStaffId = staffData.length ? normalizeId(staffData[0].id) : '';
    const defaultDate = queueDateEl ? queueDateEl.value : "{{ $selectedDate }}";

    let bookings = (Array.isArray(initialBookings) ? initialBookings : []).map(normalizeBooking);
    let bookingModal = null;
    let editingBookingId = null;
    let selectedServiceIds = [];

    function notifyError(message) {
        if (window.PFPopup && typeof window.PFPopup.error === 'function') {
            window.PFPopup.error(message);
            return;
        }
        console.error(message);
    }

    function normalizeBooking(input = {}) {
        return {
            id: normalizeId(input.id),
            customerId: normalizeId(input.customerId),
            customerName: input.customerName || '',
            serviceIds: Array.isArray(input.serviceIds)
                ? input.serviceIds.map(normalizeId).filter(Boolean)
                : (input.serviceId ? [normalizeId(input.serviceId)] : []),
            staffId: normalizeId(input.staffId),
            bedId: normalizeId(input.bedId),
            start: (input.start || '10:00').slice(0, 5),
            end: (input.end || '11:00').slice(0, 5),
            status: input.status || 'waiting',
            paid: Boolean(input.paid),
            cancelReason: input.cancelReason || null,
        };
    }

    function toMinutes(hhmm) {
        const [h, m] = hhmm.split(':').map(Number);
        return (h * 60) + m;
    }

    function toHHMM(totalMinutes) {
        const h = Math.floor(totalMinutes / 60);
        const m = totalMinutes % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    }

    function clampMinutes(totalMinutes) {
        const min = START_HOUR * 60;
        const max = END_HOUR_EXCLUSIVE * 60;
        return Math.max(min, Math.min(max, totalMinutes));
    }

    function addMinutes(time, offset) {
        return toHHMM(clampMinutes(toMinutes(time) + offset));
    }

    function getServiceDuration(serviceId) {
        const service = serviceMap.get(normalizeId(serviceId));
        return Number((service && service.duration) || 60);
    }

    function getServicePrice(serviceId) {
        const service = serviceMap.get(normalizeId(serviceId));
        return Number((service && service.price) || 0);
    }

    function getTotalDuration(serviceIds) {
        const total = serviceIds.reduce((sum, id) => sum + getServiceDuration(id), 0);
        return total > 0 ? total : 60;
    }

    function getTotalPrice(serviceIds) {
        return serviceIds.reduce((sum, id) => sum + getServicePrice(id), 0);
    }

    function getCustomerName(customerId, fallbackName = 'Walk-in') {
        const customer = customerMap.get(normalizeId(customerId));
        return (customer && customer.name) || fallbackName;
    }

    function getBedName(bedId) {
        const bed = bedMap.get(normalizeId(bedId));
        return bed ? bed.name : '';
    }

    function getServiceSummary(serviceIds) {
        const names = serviceIds
            .map(id => {
                const service = serviceMap.get(normalizeId(id));
                return service ? service.name : '';
            })
            .filter(Boolean);
        if (!names.length) return '-';
        return names[0];
    }

    function renderSelectedServices() {
        if (!selectedServiceIds.length) {
            selectedServicesEl.innerHTML = '<span class="small text-muted">ยังไม่มีบริการที่เลือก</span>';
            bookingTotalEl.textContent = '0 บ.';
            return;
        }

        selectedServicesEl.innerHTML = selectedServiceIds.map(serviceId => {
            const service = serviceMap.get(normalizeId(serviceId));
            if (!service) return '';
            return `
                <span class="selected-service-chip">
                    ${service.name}
                    <button type="button" onclick="removeService('${serviceId}')" aria-label="ลบบริการ">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </span>
            `;
        }).join('');

        bookingTotalEl.textContent = `${getTotalPrice(selectedServiceIds).toLocaleString()} บ.`;
    }

    function removeService(serviceId) {
        selectedServiceIds = selectedServiceIds.filter(id => normalizeId(id) !== normalizeId(serviceId));
        renderSelectedServices();
    }
    window.removeService = removeService;

    function ensureEndAfterStart() {
        const start = toMinutes(startTimeEl.value);
        const end = toMinutes(endTimeEl.value);
        if (end <= start) {
            endTimeEl.value = addMinutes(startTimeEl.value, getTotalDuration(selectedServiceIds));
        }
    }

    function setModalMode(isEditing) {
        bookingTitleEl.innerHTML = isEditing
            ? '<i class="bi bi-pencil-square me-2 text-primary"></i>แก้ไขคิวจอง'
            : '<i class="bi bi-journal-plus me-2 text-primary"></i>เพิ่มรายการจองใหม่';

        bookingSubtitleEl.textContent = isEditing
            ? 'แก้บริการ เวลา หมอนวด หรือชำระเงินจากคิวนี้ได้เลย'
            : 'กำหนดรายละเอียดคิวก่อนบันทึก';

        deleteBookingBtn.classList.toggle('d-none', !isEditing);
    }

    function fillModal(booking, isEditing = false) {
        customerSelectEl.value = booking.customerId || defaultCustomerId;
        staffSelectEl.value = normalizeId(booking.staffId || defaultStaffId);
        if (bedSelectEl) bedSelectEl.value = normalizeId(booking.bedId || '');
        startTimeEl.value = booking.start || '10:00';
        endTimeEl.value = booking.end || addMinutes(startTimeEl.value, getTotalDuration(booking.serviceIds));
        statusSelectEl.value = booking.status || 'waiting';
        selectedServiceIds = [...(booking.serviceIds || [])].map(normalizeId).slice(0, 1);
        if (!selectedServiceIds.length && defaultServiceId) selectedServiceIds = [defaultServiceId];
        renderSelectedServices();
        setModalMode(isEditing);
        payBookingBtn.innerHTML = booking.paid
            ? '<i class="bi bi-check2-circle me-1"></i> ชำระแล้ว'
            : '<i class="bi bi-wallet2 me-1"></i> ชำระเงิน';
        payBookingBtn.disabled = Boolean(booking.paid);
        saveBookingBtn.disabled = Boolean(booking.paid);
        payBookingBtn.classList.toggle('btn-success', booking.paid);
        payBookingBtn.classList.toggle('btn-outline-success', !booking.paid);
        deleteBookingBtn.classList.toggle('d-none', !isEditing || Boolean(booking.paid));
    }

    function renderBookings() {
        document.querySelectorAll('.booking-row-layer').forEach(l => l.innerHTML = '');
        const totalMinutes = (END_HOUR_EXCLUSIVE - START_HOUR) * 60;

        bookings.forEach(b => {
            const layer = document.getElementById(`layer-${normalizeId(b.staffId)}`);
            if (!layer) return;

            const layerWidth = layer.clientWidth;
            const layerHeight = layer.clientHeight;
            if (!layerWidth || !layerHeight) return;

            let startOffset = toMinutes(b.start) - (START_HOUR * 60);
            let endOffset = toMinutes(b.end) - (START_HOUR * 60);

            startOffset = Math.max(0, Math.min(totalMinutes - 15, startOffset));
            endOffset = Math.max(startOffset + 15, Math.min(totalMinutes, endOffset));

            const left = (startOffset / totalMinutes) * layerWidth;
            const width = Math.max(((endOffset - startOffset) / totalMinutes) * layerWidth - 4, 86);

            const card = document.createElement('div');
            card.className = `booking-card state-${b.status}`;
            card.dataset.bookingId = b.id;
            card.style.left = `${left + 2}px`;
            card.style.width = `${Math.min(width, layerWidth - left - 2)}px`;

            card.innerHTML = `
                <div class="booking-top">
                    <span class="booking-time">${b.start} - ${b.end}</span>
                    <span class="${b.paid ? 'booking-paid' : 'booking-unpaid'}">${b.paid ? 'ชำระแล้ว' : 'รอชำระ'}</span>
                </div>
                <div class="booking-customer">${getCustomerName(b.customerId, b.customerName)}</div>
                <div class="booking-service">${getServiceSummary(b.serviceIds)}${getBedName(b.bedId) ? ' · ' + getBedName(b.bedId) : ''}</div>
            `;
            card.addEventListener('click', (ev) => {
                ev.stopPropagation();
                openModal({ bookingId: b.id });
            });

            layer.appendChild(card);
        });
    }

    function getModalInstance() {
        if (!bookingModalEl) return null;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            if (!bookingModal) {
                bookingModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
            }
            return bookingModal;
        }

        return {
            show: function () {
                bookingModalEl.style.display = 'block';
                bookingModalEl.classList.add('show');
                bookingModalEl.removeAttribute('aria-hidden');
                document.body.classList.add('modal-open');

                let backdrop = document.querySelector('.modal-backdrop.pf-fallback');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show pf-fallback';
                    backdrop.addEventListener('click', () => {
                        const modal = getModalInstance();
                        if (modal && typeof modal.hide === 'function') modal.hide();
                    });
                    document.body.appendChild(backdrop);
                }
                bookingModalEl.dispatchEvent(new Event('shown.bs.modal'));
            },
            hide: function () {
                bookingModalEl.classList.remove('show');
                bookingModalEl.style.display = 'none';
                bookingModalEl.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop.pf-fallback');
                if (backdrop) backdrop.remove();
                bookingModalEl.dispatchEvent(new Event('hidden.bs.modal'));
            }
        };
    }

    function hasModalControls() {
        return customerSelectEl
            && staffSelectEl
            && bedSelectEl
            && startTimeEl
            && endTimeEl
            && statusSelectEl
            && selectedServicesEl
            && bookingTotalEl
            && bookingTitleEl
            && bookingSubtitleEl
            && payBookingBtn
            && deleteBookingBtn;
    }

    function collectBookingPayload() {
        if (!hasModalControls()) return null;
        const start = startTimeEl.value;
        const end = endTimeEl.value;
        const queueDate = (queueDateEl && queueDateEl.value) ? queueDateEl.value : defaultDate;

        if (!customerSelectEl.value || !staffSelectEl.value || !start || !end) {
            notifyError('กรุณากรอกข้อมูลให้ครบก่อนบันทึก');
            return null;
        }

        if (!selectedServiceIds.length) {
            notifyError('กรุณาเลือกอย่างน้อย 1 บริการ');
            return null;
        }

        if (toMinutes(end) <= toMinutes(start)) {
            notifyError('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม');
            return null;
        }

        return {
            branch_id: activeBranchId || null,
            queue_date: queueDate,
            customer_id: Number(customerSelectEl.value),
            service_id: Number(selectedServiceIds[0]),
            masseuse_id: staffSelectEl.value ? Number(staffSelectEl.value) : null,
            bed_id: bedSelectEl.value ? Number(bedSelectEl.value) : null,
            start_time: start,
            end_time: end,
            status: statusSelectEl.value,
            cancel_reason: null,
        };
    }

    function upsertBooking(booking) {
        const normalized = normalizeBooking(booking);
        const idx = bookings.findIndex(item => normalizeId(item.id) === normalizeId(normalized.id));
        if (idx >= 0) {
            bookings[idx] = normalized;
        } else {
            bookings.push(normalized);
            editingBookingId = normalized.id;
        }
    }

    function extractErrorMessage(errorPayload = {}) {
        if (errorPayload && typeof errorPayload.message === 'string' && errorPayload.message.trim() !== '') {
            if (!errorPayload.errors) {
                return errorPayload.message;
            }
        }

        if (errorPayload && errorPayload.errors && typeof errorPayload.errors === 'object') {
            const firstKey = Object.keys(errorPayload.errors)[0];
            if (firstKey && Array.isArray(errorPayload.errors[firstKey]) && errorPayload.errors[firstKey].length > 0) {
                return errorPayload.errors[firstKey][0];
            }
        }

        return 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            method: options.method || 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': bookingApi.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            body: options.body || null,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(extractErrorMessage(payload));
        }

        return payload;
    }

    async function saveBookingAndClose() {
        const payload = collectBookingPayload();
        if (!payload) return;

        try {
            saveBookingBtn.disabled = true;
            const isEditing = Boolean(editingBookingId);
            const url = isEditing
                ? `${bookingApi.updateBase}/${editingBookingId}`
                : bookingApi.store;
            const method = isEditing ? 'PUT' : 'POST';
            const response = await requestJson(url, {
                method,
                body: JSON.stringify(payload),
            });
            upsertBooking(response.booking);
            renderBookings();
            const modal = getModalInstance();
            if (modal && typeof modal.hide === 'function') {
                modal.hide();
            }
        } catch (error) {
            notifyError(error.message);
        } finally {
            saveBookingBtn.disabled = false;
        }
    }

    function markAsPaid() {
        const payload = collectBookingPayload();
        if (!payload) return;

        const params = new URLSearchParams({
            from_booking: '1',
            queue_date: payload.queue_date,
            customer_id: String(payload.customer_id),
            staff_id: payload.masseuse_id !== null ? String(payload.masseuse_id) : '',
            service_id: String(payload.service_id),
            bed_id: payload.bed_id !== null ? String(payload.bed_id) : '',
            start_time: payload.start_time,
            end_time: payload.end_time,
        });

        if (activeBranchId) {
            params.set('branch_id', String(activeBranchId));
        }

        if (editingBookingId) {
            params.set('booking_id', String(editingBookingId));
        }

        window.location.href = `${bookingApi.pos}?${params.toString()}`;
    }

    async function deleteBooking() {
        if (!editingBookingId) return;

        try {
            deleteBookingBtn.disabled = true;
            const deleteUrl = activeBranchId
                ? `${bookingApi.updateBase}/${editingBookingId}?branch_id=${encodeURIComponent(String(activeBranchId))}`
                : `${bookingApi.updateBase}/${editingBookingId}`;
            await requestJson(deleteUrl, {
                method: 'DELETE',
            });
            bookings = bookings.filter(item => normalizeId(item.id) !== normalizeId(editingBookingId));
            renderBookings();
            const modal = getModalInstance();
            if (modal && typeof modal.hide === 'function') {
                modal.hide();
            }
        } catch (error) {
            notifyError(error.message);
        } finally {
            deleteBookingBtn.disabled = false;
        }
    }

    async function loadBookingsByDate(dateValue) {
        const params = new URLSearchParams({
            date: dateValue || defaultDate,
        });
        if (activeBranchId) {
            params.set('branch_id', String(activeBranchId));
        }

        try {
            const response = await requestJson(`${bookingApi.data}?${params.toString()}`);
            bookings = (response.bookings || []).map(normalizeBooking);
            renderBookings();
        } catch (error) {
            notifyError(error.message);
        }
    }

    function scheduleRenderBookings() {
        requestAnimationFrame(renderBookings);
    }

    function openModal(data = {}) {
        const modalInstance = getModalInstance();
        if (!modalInstance) {
            notifyError('ไม่สามารถเปิดหน้าต่างคิวได้ กรุณารีเฟรชหน้าอีกครั้ง');
            return;
        }
        if (!hasModalControls()) {
            notifyError('ไม่พบฟอร์มจัดการคิวในหน้านี้');
            return;
        }

        if (data.bookingId) {
            const current = bookings.find(item => item.id === data.bookingId);
            if (!current) return;
            editingBookingId = current.id;
            fillModal(current, true);
            modalInstance.show();
            return;
        }

        editingBookingId = null;
        const startTime = (data.time || '10:00').slice(0, 5);
        const initialServices = defaultServiceId ? [defaultServiceId] : [];
        const duration = getTotalDuration(initialServices);

        fillModal({
            customerId: defaultCustomerId,
            serviceIds: initialServices,
            staffId: normalizeId(data.staffId || defaultStaffId),
            bedId: '',
            start: startTime,
            end: addMinutes(startTime, duration),
            status: 'waiting',
            paid: false
        }, false);

        modalInstance.show();
    }
    window.openModal = openModal;

    if (queueBoardEl) {
        queueBoardEl.addEventListener('click', (e) => {
            const card = e.target.closest('.booking-card');
            if (card && card.dataset.bookingId) {
                openModal({ bookingId: card.dataset.bookingId });
                return;
            }

            const slot = e.target.closest('.queue-slot-cell');
            if (slot) {
                const row = slot.closest('.queue-data-row');
                openModal({
                    staffId: (row && row.dataset.staffId) || defaultStaffId,
                    time: slot.dataset.time || '10:00'
                });
                return;
            }

            const layer = e.target.closest('.booking-row-layer');
            if (layer && e.target === layer) {
                const rect = layer.getBoundingClientRect();
                const relativeX = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
                const totalMinutes = (END_HOUR_EXCLUSIVE - START_HOUR) * 60;
                const offsetMinutes = Math.floor((relativeX / Math.max(rect.width, 1)) * totalMinutes / 60) * 60;
                const time = toHHMM((START_HOUR * 60) + offsetMinutes);
                openModal({
                    staffId: layer.dataset.staffId || defaultStaffId,
                    time
                });
            }
        });
    }

    if (addServiceBtn) {
        addServiceBtn.addEventListener('click', () => {
            const serviceId = normalizeId(serviceSelectEl.value);
            if (!serviceId) return;
            selectedServiceIds = [serviceId];
            renderSelectedServices();
            ensureEndAfterStart();
        });
    }

    if (startTimeEl) startTimeEl.addEventListener('change', ensureEndAfterStart);
    if (endTimeEl) endTimeEl.addEventListener('change', ensureEndAfterStart);
    if (saveBookingBtn) saveBookingBtn.addEventListener('click', saveBookingAndClose);
    if (payBookingBtn) payBookingBtn.addEventListener('click', markAsPaid);
    if (deleteBookingBtn) deleteBookingBtn.addEventListener('click', deleteBooking);

    if (bookingModalEl) {
        bookingModalEl.addEventListener('hidden.bs.modal', () => {
            if (bookingFormEl) bookingFormEl.reset();
            selectedServiceIds = [];
            editingBookingId = null;
            if (saveBookingBtn) saveBookingBtn.disabled = false;
            if (payBookingBtn) payBookingBtn.disabled = false;
            if (deleteBookingBtn) deleteBookingBtn.disabled = false;
        });
    }

    if (customerPhoneEl) {
        customerPhoneEl.addEventListener('input', () => {
            const rawDigits = customerPhoneEl.value.replace(/\D/g, '');
            if (rawDigits.length < 3) {
                if (customerPhoneHintEl) {
                    customerPhoneHintEl.textContent = 'พิมพ์อย่างน้อย 3 ตัวเลขเพื่อเลือกข้อมูลลูกค้าเดิมอัตโนมัติ';
                }
                return;
            }

            const found = customerData.find(customer => {
                const customerPhone = String(customer.phone || '').replace(/\D/g, '');
                return customerPhone.includes(rawDigits);
            });

            if (!found) {
                if (customerPhoneHintEl) {
                    customerPhoneHintEl.textContent = 'ไม่พบลูกค้าจากเบอร์นี้';
                }
                return;
            }

            customerSelectEl.value = normalizeId(found.id);
            if (customerPhoneHintEl) {
                customerPhoneHintEl.textContent = `พบลูกค้า: ${found.name}`;
            }
        });
    }

    if (queueDateEl) {
        queueDateEl.addEventListener('change', () => {
            loadBookingsByDate(queueDateEl.value);
        });
    }

    window.addEventListener('resize', scheduleRenderBookings);

    document.addEventListener('DOMContentLoaded', () => {
        scheduleRenderBookings();
    });
</script>
@endpush
