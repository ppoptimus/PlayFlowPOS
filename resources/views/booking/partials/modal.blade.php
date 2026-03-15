<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>เพิ่มรายการจองใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="booking-form" class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">เลือกลูกค้า</label>
                        <select class="form-select rounded-3 shadow-none" id="customer-select" required>
                            @foreach($customers as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }} ({{ $c['phone'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">บริการ</label>
                        <select class="form-select rounded-3 shadow-none" id="service-select" required>
                            @foreach($serviceItems as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }} ({{ $s['duration'] }} นาที)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">หมอนวด</label>
                        <select class="form-select rounded-3 shadow-none" id="staff-select" required>
                            @foreach($staff as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">เวลาเริ่ม</label>
                        <input type="time" class="form-control rounded-3 shadow-none" id="start-time" value="10:00" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="alert('จองสำเร็จ (Mockup)')">บันทึกการจอง</button>
            </div>
        </div>
    </div>
</div>