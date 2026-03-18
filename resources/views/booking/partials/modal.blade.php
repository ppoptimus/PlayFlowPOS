<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold mb-1" id="booking-modal-title">
                        <i class="bi bi-journal-plus me-2 text-primary"></i>เพิ่มรายการจองใหม่
                    </h5>
                    <span class="badge rounded-pill text-bg-light border" id="booking-modal-subtitle">กำหนดรายละเอียดคิวก่อนบันทึก</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="booking-form" class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">ค้นหาลูกค้าจากเบอร์โทร</label>
                        <input type="tel" class="form-control rounded-3 shadow-none" id="customer-phone" placeholder="พิมพ์เบอร์ เช่น 0891111111">
                        <div class="form-text" id="customer-phone-hint">พิมพ์อย่างน้อย 3 ตัวเลขเพื่อเลือกข้อมูลลูกค้าเดิมอัตโนมัติ</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">ลูกค้า</label>
                        <select class="form-select rounded-3 shadow-none" id="customer-select" required>
                            @foreach($customers as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }} ({{ $c['phone'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">หมอนวด</label>
                        <select class="form-select rounded-3 shadow-none" id="staff-select" required>
                            @foreach($staff as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" id="staff-availability-hint">เลือกช่วงเวลาเพื่อเช็กคิวหมอนวด</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">ห้อง/เตียง</label>
                        <select class="form-select rounded-3 shadow-none" id="bed-select">
                            <option value="">ไม่ระบุ</option>
                            @foreach($beds as $bed)
                            <option value="{{ $bed['id'] }}">{{ $bed['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" id="bed-availability-hint">เลือกช่วงเวลาเพื่อเช็กคิวเตียง</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">เวลาเริ่ม</label>
                        <input type="time" class="form-control rounded-3 shadow-none" id="start-time" value="10:00" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">เวลาสิ้นสุด</label>
                        <input type="time" class="form-control rounded-3 shadow-none" id="end-time" value="11:00" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">สถานะคิว</label>
                        <select class="form-select rounded-3 shadow-none" id="status-select">
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">เพิ่มบริการ</label>
                        <div class="input-group">
                            <select class="form-select rounded-start-3 shadow-none" id="service-select">
                                @foreach($serviceItems as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }} ({{ $s['duration'] }} นาที)</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="add-service-btn">
                                <i class="bi bi-plus-lg"></i> เพิ่ม
                            </button>
                        </div>
                        <div class="form-text" id="service-selection-hint">เพิ่มบริการได้สูงสุด 3 รายการ</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">รายการบริการที่เลือก</label>
                        <div id="selected-services" class="d-flex flex-wrap gap-2 p-2 border rounded-3 bg-light-subtle"></div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center rounded-3 border px-3 py-2 bg-light-subtle">
                            <div>
                                <div class="small text-muted">ยอดชำระโดยประมาณ</div>
                                <div class="small text-muted">คำนวณจากบริการที่เลือก</div>
                            </div>
                            <div class="fw-bold fs-5 text-primary" id="booking-total">0 บ.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger rounded-pill px-3 d-none" id="delete-booking-btn">
                    <i class="bi bi-trash3 me-1"></i> ลบคิว
                </button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-outline-success rounded-pill px-3" id="pay-booking-btn">
                        <i class="bi bi-wallet2 me-1"></i> ชำระเงิน
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="save-booking-btn">บันทึก</button>
                </div>
            </div>
        </div>
    </div>
</div>
