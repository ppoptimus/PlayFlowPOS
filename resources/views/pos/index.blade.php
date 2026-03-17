@extends('layouts.main')

@section('title', 'POS - PlayFlow POS')
@section('page_title', 'หน้าจอขาย')
@section('page_subtitle', 'สุขุมวิท | Manager')

@section('content')
<div class="row g-3 pos-mobile-safe">
    <div class="col-12 col-lg-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <label class="form-label fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px;">หมวดหมู่</label>
                <div class="dropdown w-100">
                    <button class="btn w-100 text-start d-flex justify-content-between align-items-center shadow-sm border rounded-4 py-3 fw-bold bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="categoryDropdownBtn">
                        <span class="d-flex align-items-center"><i class="bi bi-grid-fill me-2 text-primary fs-5"></i> <span class="ms-1">ทั้งหมด</span></span>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </button>
                    <ul class="dropdown-menu w-100 border-0 shadow-lg rounded-4 p-2 mt-2">
                        <li><a class="dropdown-item rounded-3 py-2 fw-semibold active tab-filter d-flex align-items-center mb-1" href="#" data-filter="all">
                            <i class="bi bi-grid-fill me-2 text-primary fs-5"></i> ทั้งหมด
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-2 fw-semibold tab-filter d-flex align-items-center mb-1" href="#" data-filter="service">
                            <i class="bi bi-person-walking me-2 text-info fs-5"></i> บริการนวด
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-2 fw-semibold tab-filter d-flex align-items-center" href="#" data-filter="product">
                            <i class="bi bi-box-seam me-2 text-success fs-5"></i> สินค้าปลีก
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-2 fw-semibold tab-filter d-flex align-items-center" href="#" data-filter="package">
                            <i class="bi bi-wallet me-2 text-success fs-5"></i> ซื้อแพคเกจเพิ่ม
                        </a></li>
                    </ul>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px;">ค้นหารายการ</label>
                    <input type="text" id="item-search" class="form-control rounded-3 shadow-sm" placeholder="พิมพ์ชื่อบริการ/สินค้า">
                </div>

                <hr class="text-black-50 my-3 opacity-25">
                
                <label class="form-label fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px;">ข้อมูลลูกค้า</label>
                <button class="btn w-100 text-start d-flex justify-content-between align-items-center shadow-sm border border-primary-subtle rounded-4 py-3 fw-bold bg-primary-subtle text-primary" type="button" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                    <span class="d-flex align-items-center"><i class="bi bi-person-plus-fill me-2 fs-5"></i> <span class="ms-1">เพิ่มลูกค้าใหม่</span></span>
                </button>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-body p-3 overflow-auto" style="max-height: 75vh;">
                <div class="row g-2" id="item-grid">
                    @foreach($items as $item)
                    <div class="col-6 col-md-4 item-card-wrap" data-type="{{ $item['type'] }}" data-name="{{ strtolower($item['name']) }}">
                        <div class="card h-100 border shadow-none rounded-4 item-card"
                             data-item-id="{{ $item['id'] }}"
                             style="cursor: pointer;"
                             onclick='addToCart(@json($item["id"]), @json($item["name"]), {{ $item["price"] }}, @json($item["type"]), {{ $item["source_id"] }})'>
                            <div class="card-body p-3">
                                <span class="badge {{ $item['type'] == 'service' ? 'bg-info-subtle text-info' : 'bg-success-subtle text-success' }} mb-2 rounded-pill">
                                    {{ $item['type'] == 'service' ? 'Service' : 'Product' }}
                                </span>
                                <h6 class="fw-bold mb-1 text-truncate">{{ $item['name'] }}</h6>
                                <div class="d-flex justify-content-between align-items-end mt-3">
                                    <small class="text-muted">{{ $item['duration'] ? $item['duration'].' นาที' : 'Retail' }}</small>
                                    <span class="fw-bold text-primary">{{ number_format($item['price']) }}฿</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3 d-flex flex-column">
                <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i> รายการปัจจุบัน</h5>
                <div id="booking-context-banner" class="alert alert-info rounded-3 py-2 d-none">
                    <strong>รับมาจากหน้าจองคิว</strong><br>
                    <span id="booking-context-text" class="small"></span>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="small fw-bold text-muted">ลูกค้า</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                            <input type="text" id="customer-name-input" class="form-control rounded-end-3" placeholder="Walk-in / ชื่อหรือเบอร์">
                        </div>
                        <input type="hidden" id="customer-id-hidden" value="">
                        <div class="small text-muted mt-1" id="customer-match-hint">Walk-in</div>
                    </div>
                    <div class="col-5">
                        <label class="small fw-bold text-muted">หมอนวด/ผู้ขาย</label>
                        <select id="staff-select" class="form-select form-select-sm rounded-3">
                            @foreach($staff as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex-grow-1 overflow-auto border-top border-bottom py-2" style="max-height: 35vh;" id="cart-list">
                    <div class="text-center text-muted py-5" id="empty-cart-msg">ยังไม่มีรายการในบิล</div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">รวมเงิน</span>
                        <span class="fw-bold" id="subtotal">0.00 ฿</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="text-muted">ส่วนลด</span>
                        <div class="input-group input-group-sm w-50">
                            <input type="number" id="discount-input" class="form-control text-end" value="0">
                            <span class="input-group-text bg-light">฿</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="text-muted">คอร์สโปรโมชั่น</span>
                        <div class="input-group input-group-sm w-50">
                            <input type="text" id="promotion-input" class="form-control text-end" value="">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    
                    <div class="bg-primary-subtle p-3 rounded-4 mb-3 text-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">ยอดสุทธิ</h5>
                        <h4 class="mb-0 fw-bold" id="grand-total">0.00 ฿</h4>
                    </div>

                    <div class="row g-2">
                        <div class="col-4 text-center">
                            <button class="btn btn-outline-secondary w-100 rounded-3 py-2 active payment-btn" data-pay="cash">
                                <i class="bi bi-cash d-block fs-4"></i> เงินสด
                            </button>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-outline-secondary w-100 rounded-3 py-2 payment-btn" data-pay="transfer">
                                <i class="bi bi-qr-code-scan d-block fs-4"></i> โอนเงิน
                            </button>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-outline-secondary w-100 rounded-3 py-2 payment-btn" data-pay="card">
                                <i class="bi bi-credit-card d-block fs-4"></i> บัตร
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg rounded-pill mt-3 py-3 fw-bold shadow-sm" id="checkout-btn" onclick="checkout()">
                        <i class="bi bi-check-circle-fill me-2"></i> ชำระเงิน
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #f4f7f9;">
            <div class="modal-header border-0 bg-white px-4 pt-4 pb-2">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">เพิ่มข้อมูลลูกค้าใหม่</h5>
                        <small class="text-muted">บันทึกประวัติการใช้บริการและสะสมแต้ม</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <form id="new-customer-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">ชื่อจริง <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0 fs-6" placeholder="ชื่อจริง">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0 fs-6" placeholder="นามสกุล">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">ชื่อเล่น</label>
                            <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0 fs-6" placeholder="ชื่อเล่น">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">เพศ</label>
                            <select class="form-select form-select-lg rounded-3 shadow-sm border-0 fs-6 text-muted">
                                <option value="" selected disabled>เลือกเพศ</option>
                                <option value="M">ชาย (Male)</option>
                                <option value="F">หญิง (Female)</option>
                                <option value="O">อื่นๆ (Other)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">อายุ (ปี)</label>
                            <input type="number" class="form-control form-control-lg rounded-3 shadow-sm border-0 fs-6" placeholder="ระบุอายุ">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control border-0 fs-6" placeholder="08X-XXX-XXXX">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Line ID</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 text-success"><i class="bi bi-line"></i></span>
                                <input type="text" class="form-control border-0 fs-6" placeholder="LINE ID ของลูกค้า">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">ที่อยู่</label>
                            <textarea class="form-control rounded-3 shadow-sm border-0 p-3 fs-6" rows="2" placeholder="รายละเอียดที่อยู่..."></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">หมายเหตุ / ข้อมูลเพิ่มเติม</label>
                            <textarea class="form-control rounded-3 shadow-sm border-0 p-3 fs-6" rows="2" placeholder="เช่น อาการแพ้, โรคประจำตัว, น้ำหนักมือที่ชอบ..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 bg-transparent justify-content-end">
                <button type="button" class="btn btn-white text-muted rounded-pill px-4 fw-bold shadow-sm border me-2" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="saveNewCustomer()"><i class="bi bi-check-circle-fill me-2"></i> บันทึกลูกค้า</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printChoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-printer me-2 text-primary"></i>พิมพ์ใบเสร็จ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">ชำระเงินสำเร็จแล้ว</p>
                <div class="small text-muted mb-3">เลขที่บิล: <span id="print-choice-order-no" class="fw-semibold">-</span></div>
                <div class="alert alert-light border rounded-3 mb-0">
                    ต้องการพิมพ์ใบเสร็จตอนนี้หรือไม่
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="skip-print-btn">ไม่พิมพ์ตอนนี้</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="print-now-btn">
                    <i class="bi bi-printer me-1"></i> พิมพ์ตอนนี้
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .item-card {
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }
    .item-card:hover {
        border-color: rgba(31, 115, 224, 0.35) !important;
        transform: translateY(-1px);
    }
    .item-card.is-selected {
        border-color: #2d8ff0 !important;
        box-shadow: 0 10px 22px rgba(31, 115, 224, 0.2) !important;
        background: linear-gradient(180deg, #f4f9ff 0%, #ffffff 100%);
    }
    .item-card.is-selected .badge {
        box-shadow: 0 0 0 1px rgba(45, 143, 240, 0.25) inset;
    }
</style>
@endpush

@push('scripts')
<script>
    let cart = [];

    const bookingContext = @json($bookingContext);
    const serviceItems = @json($serviceItems);
    const customers = @json($customers ?? []);
    const checkoutUrl = "{{ route('pos.checkout') }}";
    const quickCreateCustomerUrl = "{{ route('customers.quick-create') }}";
    const bookingUrl = "{{ route('booking') }}";
    const receiptDetailBaseUrl = "{{ url('/receipts') }}";
    const csrfToken = "{{ csrf_token() }}";
    const activeBranchId = @json($activeBranchId ?? null);

    const customerNameInputEl = document.getElementById('customer-name-input');
    const customerIdHiddenEl = document.getElementById('customer-id-hidden');
    const customerMatchHintEl = document.getElementById('customer-match-hint');
    const staffSelectEl = document.getElementById('staff-select');
    const discountInputEl = document.getElementById('discount-input');
    const checkoutBtn = document.getElementById('checkout-btn');
    const bookingBannerEl = document.getElementById('booking-context-banner');
    const bookingBannerTextEl = document.getElementById('booking-context-text');
    const printChoiceModalEl = document.getElementById('printChoiceModal');
    const printChoiceOrderNoEl = document.getElementById('print-choice-order-no');
    const printNowBtn = document.getElementById('print-now-btn');
    const skipPrintBtn = document.getElementById('skip-print-btn');
    const newCustomerFormEl = document.getElementById('new-customer-form');
    let printChoiceResolver = null;

    function notifySuccess(message) {
        if (window.PFPopup && typeof window.PFPopup.success === 'function') {
            window.PFPopup.success(message);
            return;
        }
        console.log(message);
    }

    function notifyError(message) {
        if (window.PFPopup && typeof window.PFPopup.error === 'function') {
            window.PFPopup.error(message);
            return;
        }
        console.error(message);
    }

    function notifyWarning(message) {
        if (window.PFPopup && typeof window.PFPopup.warning === 'function') {
            window.PFPopup.warning(message);
            return;
        }
        console.warn(message);
    }

    function notifyInfo(message) {
        if (window.PFPopup && typeof window.PFPopup.info === 'function') {
            window.PFPopup.info(message);
            return;
        }
        console.info(message);
    }

    function extractErrorMessage(payload, fallbackMessage) {
        if (payload && payload.errors && typeof payload.errors === 'object') {
            const firstKey = Object.keys(payload.errors)[0];
            if (firstKey && Array.isArray(payload.errors[firstKey]) && payload.errors[firstKey][0]) {
                return String(payload.errors[firstKey][0]);
            }
        }

        if (payload && typeof payload.message === 'string' && payload.message.trim() !== '') {
            return payload.message;
        }

        return fallbackMessage;
    }

    function addToCart(id, name, price, type, sourceId) {
        const index = cart.findIndex(item => item.id === id);
        if (index > -1) {
            cart[index].qty++;
        } else {
            cart.push({
                id,
                sourceId: Number(sourceId),
                name,
                price: Number(price),
                type,
                qty: 1,
            });
        }
        renderCart();
    }

    function syncItemCardHighlights() {
        const selectedIds = new Set(cart.map(item => item.id));
        document.querySelectorAll('.item-card').forEach(card => {
            const cardId = card.dataset.itemId || '';
            card.classList.toggle('is-selected', selectedIds.has(cardId));
        });
    }

    function normalizeCustomerText(value) {
        return String(value || '').trim().toLowerCase();
    }

    function normalizePhone(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function updateCustomerMatchHint(message, state = 'muted') {
        if (!customerMatchHintEl) return;

        customerMatchHintEl.classList.remove('text-muted', 'text-success', 'text-warning');
        if (state === 'success') {
            customerMatchHintEl.classList.add('text-success');
        } else if (state === 'warning') {
            customerMatchHintEl.classList.add('text-warning');
        } else {
            customerMatchHintEl.classList.add('text-muted');
        }

        customerMatchHintEl.textContent = message;
    }

    function findMatchedCustomer(rawInput) {
        const typedText = normalizeCustomerText(rawInput);
        if (typedText === '') return null;

        const typedPhone = normalizePhone(rawInput);
        if (typedPhone !== '') {
            const matchByPhone = customers.find((customer) => normalizePhone(customer.phone) === typedPhone);
            if (matchByPhone) {
                return matchByPhone;
            }
        }

        return customers.find((customer) => normalizeCustomerText(customer.name) === typedText) || null;
    }

    function getSelectedCustomerId() {
        if (!customerIdHiddenEl || customerIdHiddenEl.value === '') return null;
        const parsed = Number(customerIdHiddenEl.value);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
    }

    function syncCustomerIdentityFromInput() {
        if (!customerNameInputEl || !customerIdHiddenEl) return;

        const value = customerNameInputEl.value.trim();
        if (value === '' || value.toLowerCase() === 'walk-in') {
            customerIdHiddenEl.value = '';
            updateCustomerMatchHint('Walk-in', 'muted');
            return;
        }

        const matchedCustomer = findMatchedCustomer(value);
        if (!matchedCustomer) {
            customerIdHiddenEl.value = '';
            updateCustomerMatchHint('ลูกค้าใหม่ (ไม่ผูก CRM)', 'warning');
            return;
        }

        customerIdHiddenEl.value = String(matchedCustomer.id);
        updateCustomerMatchHint(`ผูก CRM: ${matchedCustomer.name}`, 'success');
    }

    function remove(id) {
        cart = cart.filter(item => item.id !== id);
        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) {
            remove(id);
            return;
        }
        renderCart();
    }

    function renderCart() {
        const cartList = document.getElementById('cart-list');

        if (cart.length === 0) {
            cartList.innerHTML = '<div class="text-center text-muted py-5" id="empty-cart-msg">ยังไม่มีรายการในบิล</div>';
            calculate();
            syncItemCardHighlights();
            return;
        }

        cartList.innerHTML = cart.map(item => `
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded-3">
                <div class="flex-grow-1">
                    <div class="fw-bold small">${item.name}</div>
                    <small class="text-muted">${item.price.toLocaleString()} ฿</small>
                </div>
                <div class="d-flex align-items-center me-2">
                    <button class="btn btn-sm btn-white border rounded-circle p-0" style="width:24px;height:24px" onclick="updateQty('${item.id}', -1)">-</button>
                    <span class="mx-2 fw-bold">${item.qty}</span>
                    <button class="btn btn-sm btn-white border rounded-circle p-0" style="width:24px;height:24px" onclick="updateQty('${item.id}', 1)">+</button>
                </div>
                <div class="fw-bold text-end" style="min-width: 60px;">${(item.price * item.qty).toLocaleString()}</div>
                <button class="btn btn-sm text-danger ms-2" onclick="remove('${item.id}')"><i class="bi bi-trash"></i></button>
            </div>
        `).join('');

        calculate();
        syncItemCardHighlights();
    }

    function calculate() {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        const discount = parseFloat(discountInputEl.value) || 0;
        const total = Math.max(0, subtotal - discount);

        document.getElementById('subtotal').innerText = subtotal.toLocaleString() + ' ฿';
        document.getElementById('grand-total').innerText = total.toLocaleString() + ' ฿';
    }

    function getActivePaymentMethod() {
        const activeButton = document.querySelector('.payment-btn.active');
        if (!activeButton) return 'cash';
        return activeButton.dataset.pay || 'cash';
    }

    function toCheckoutBookingContext() {
        if (!bookingContext || bookingContext.fromBooking !== true) {
            return null;
        }

        return {
            booking_id: bookingContext.bookingId || null,
            queue_date: bookingContext.queueDate || null,
            start_time: bookingContext.startTime || null,
            end_time: bookingContext.endTime || null,
            customer_id: getSelectedCustomerId() ?? (bookingContext.customerId || null),
            staff_id: staffSelectEl && staffSelectEl.value ? Number(staffSelectEl.value) : (bookingContext.staffId || null),
            service_id: bookingContext.serviceId || null,
            bed_id: bookingContext.bedId || null,
            is_paid: Boolean(bookingContext.isPaid),
        };
    }

    function escapeHtml(input) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        };
        return String(input || '').replace(/[&<>"']/g, (m) => map[m]);
    }

    function buildReceiptHtml(receipt) {
        const rows = (receipt.items || []).map((item) => {
            return `
                <tr>
                    <td>${escapeHtml(item.item_name)}</td>
                    <td style="text-align:right;">${Number(item.qty || 0).toLocaleString('th-TH')}</td>
                    <td style="text-align:right;">${Number(item.unit_price || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td style="text-align:right;">${Number(item.line_total || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `;
        }).join('');

        return `
            <!doctype html>
            <html lang="th">
            <head>
                <meta charset="utf-8">
                <title>${escapeHtml(receipt.order_no || '')}</title>
                <style>
                    body { font-family: "Prompt", sans-serif; margin: 20px; color: #173b59; }
                    h2 { margin: 0 0 8px; }
                    .muted { color: #54708a; font-size: 12px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                    th, td { border-bottom: 1px solid #dce7f2; padding: 8px 4px; font-size: 13px; }
                    th { text-align: left; color: #305d84; }
                    .totals { margin-top: 12px; }
                    .totals div { display: flex; justify-content: space-between; margin: 4px 0; }
                    .grand { font-weight: 700; font-size: 18px; color: #0f65b8; }
                </style>
            </head>
            <body>
                <h2>ใบเสร็จรับเงิน</h2>
                <div class="muted">เลขที่บิล: ${escapeHtml(receipt.order_no || '-')}</div>
                <div class="muted">วันที่: ${escapeHtml(receipt.created_at || '-')}</div>
                <div class="muted">ลูกค้า: ${escapeHtml(receipt.customer_name || 'Walk-in')}</div>
                <div class="muted">ชำระโดย: ${escapeHtml(receipt.payment_method_label || '-')}</div>
                <table>
                    <thead>
                        <tr>
                            <th>รายการ</th>
                            <th style="text-align:right;">จำนวน</th>
                            <th style="text-align:right;">ราคา/หน่วย</th>
                            <th style="text-align:right;">รวม</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
                <div class="totals">
                    <div><span>รวมเงิน</span><span>${Number(receipt.total_amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ฿</span></div>
                    <div><span>ส่วนลด</span><span>${Number(receipt.discount_amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ฿</span></div>
                    <div class="grand"><span>ยอดสุทธิ</span><span>${Number(receipt.grand_total || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ฿</span></div>
                </div>
            </body>
            </html>
        `;
    }

    async function printReceiptByOrderId(orderId) {
        if (!orderId) return false;

        const params = new URLSearchParams();
        if (activeBranchId) {
            params.set('branch_id', String(activeBranchId));
        }

        const response = await fetch(`${receiptDetailBaseUrl}/${orderId}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || 'โหลดข้อมูลใบเสร็จเพื่อพิมพ์ไม่สำเร็จ');
        }

        const printWindow = window.open('', '_blank');

        if (!printWindow) {
            throw new Error('เบราว์เซอร์บล็อกหน้าต่างพิมพ์อัตโนมัติ');
        }

        printWindow.document.open();
        printWindow.document.write(buildReceiptHtml(payload));
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        return true;
    }

    function showPrintChoiceModal() {
        if (!printChoiceModalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(printChoiceModalEl).show();
            return;
        }
        printChoiceModalEl.style.display = 'block';
        printChoiceModalEl.classList.add('show');
        printChoiceModalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
    }

    function hidePrintChoiceModal() {
        if (!printChoiceModalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(printChoiceModalEl);
            modal.hide();
            return;
        }
        printChoiceModalEl.classList.remove('show');
        printChoiceModalEl.style.display = 'none';
        printChoiceModalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function resolvePrintChoice(shouldPrint) {
        if (!printChoiceResolver) return;
        const resolver = printChoiceResolver;
        printChoiceResolver = null;
        hidePrintChoiceModal();
        resolver(shouldPrint);
    }

    function askPrintChoice(orderNo) {
        if (!printChoiceModalEl) {
            return Promise.resolve(false);
        }

        if (printChoiceOrderNoEl) {
            printChoiceOrderNoEl.textContent = orderNo || '-';
        }

        showPrintChoiceModal();
        return new Promise((resolve) => {
            printChoiceResolver = resolve;
        });
    }

    async function checkout() {
        if (cart.length === 0) {
            notifyError('กรุณาเลือกรายการสินค้า');
            return;
        }

        syncCustomerIdentityFromInput();

        const payload = {
            customer_id: getSelectedCustomerId(),
            staff_id: staffSelectEl.value ? Number(staffSelectEl.value) : null,
            discount_amount: parseFloat(discountInputEl.value) || 0,
            payment_method: getActivePaymentMethod(),
            items: cart.map(item => ({
                type: item.type,
                source_id: item.sourceId,
                qty: item.qty,
            })),
            booking_context: toCheckoutBookingContext(),
        };

        try {
            checkoutBtn.disabled = true;
            const response = await fetch(checkoutUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = (data.errors && Object.values(data.errors)[0] && Object.values(data.errors)[0][0])
                    ? Object.values(data.errors)[0][0]
                    : (data.message || 'ชำระเงินไม่สำเร็จ');
                throw new Error(message);
            }

            notifySuccess(`ชำระเงินสำเร็จ\nเลขที่บิล: ${data.order_no}`);
            const shouldPrint = await askPrintChoice(data.order_no);

            if (shouldPrint) {
                try {
                    await printReceiptByOrderId(data.order_id);
                    notifyInfo('ส่งคำสั่งพิมพ์ใบเสร็จแล้ว');
                } catch (printError) {
                    notifyWarning(`พิมพ์ไม่สำเร็จ: ${printError.message}`);
                    notifyInfo('สามารถพิมพ์ย้อนหลังได้ที่เมนู ใบเสร็จ');
                }
            } else {
                notifyInfo('ยังไม่พิมพ์ใบเสร็จ สามารถพิมพ์ย้อนหลังได้ที่เมนู ใบเสร็จ');
            }

            if (bookingContext && bookingContext.fromBooking === true) {
                const returnDate = bookingContext.queueDate || '';
                setTimeout(() => {
                    window.location.href = returnDate
                        ? `${bookingUrl}?date=${encodeURIComponent(returnDate)}`
                        : bookingUrl;
                }, 1000);
                return;
            }

            cart = [];
            renderCart();
        } catch (error) {
            notifyError(error.message);
        } finally {
            checkoutBtn.disabled = false;
        }
    }

    function applyBookingContext() {
        syncCustomerIdentityFromInput();

        if (!bookingContext || bookingContext.fromBooking !== true) {
            return;
        }

        if (bookingBannerEl) {
            bookingBannerEl.classList.remove('d-none');
            if (bookingBannerTextEl) {
                bookingBannerTextEl.textContent = `วันที่ ${bookingContext.queueDate || '-'} เวลา ${bookingContext.startTime || '-'}-${bookingContext.endTime || '-'} น.`;
            }
        }

        if (bookingContext.customerId) {
            const bookingCustomer = customers.find((customer) => Number(customer.id) === Number(bookingContext.customerId));
            if (customerNameInputEl) {
                customerNameInputEl.value = bookingCustomer
                    ? String(bookingCustomer.name || '')
                    : `Customer #${bookingContext.customerId}`;
            }
            if (customerIdHiddenEl) {
                customerIdHiddenEl.value = String(bookingContext.customerId);
            }
            updateCustomerMatchHint(
                bookingCustomer ? `ผูก CRM: ${bookingCustomer.name}` : 'ผูก CRM จากคิวเดิม',
                'success'
            );
        }

        if (bookingContext.staffId && staffSelectEl) {
            staffSelectEl.value = String(bookingContext.staffId);
        }

        if (bookingContext.serviceId && cart.length === 0) {
            const service = serviceItems.find(item => Number(item.source_id) === Number(bookingContext.serviceId));
            if (service) {
                addToCart(service.id, service.name, Number(service.price), 'service', Number(service.source_id));
            }
        }

        if (bookingContext.isPaid === true && checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> คิวนี้ชำระแล้ว';
        }
    }

    document.querySelectorAll('.tab-filter').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.tab-filter').forEach(b => {
                b.classList.remove('active', 'bg-primary-subtle');
            });
            btn.classList.add('active', 'bg-primary-subtle');

            const iconHtml = btn.querySelector('i').outerHTML;
            const textHtml = btn.textContent.trim();
            document.getElementById('categoryDropdownBtn').innerHTML = `<span class="d-flex align-items-center">${iconHtml} <span class="ms-1">${textHtml}</span></span> <i class="bi bi-chevron-down text-muted"></i>`;

            const filter = btn.dataset.filter;
            document.querySelectorAll('.item-card-wrap').forEach(item => {
                item.style.display = (filter === 'all' || item.dataset.type === filter) ? 'block' : 'none';
            });
        });
    });

    const itemSearchInput = document.getElementById('item-search');
    if (itemSearchInput) {
        itemSearchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.item-card-wrap').forEach(item => {
                const name = item.dataset.name;
                item.style.display = name.includes(term) ? 'block' : 'none';
            });
        });
    }

    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.payment-btn').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-secondary');
        });
    });

    if (discountInputEl) {
        discountInputEl.addEventListener('input', calculate);
    }

    if (customerNameInputEl) {
        customerNameInputEl.addEventListener('input', () => {
            syncCustomerIdentityFromInput();
        });

        customerNameInputEl.addEventListener('blur', () => {
            if (!customerNameInputEl.value.trim()) {
                updateCustomerMatchHint('Walk-in', 'muted');
            }
        });
    }

    if (printNowBtn) {
        printNowBtn.addEventListener('click', () => resolvePrintChoice(true));
    }

    if (skipPrintBtn) {
        skipPrintBtn.addEventListener('click', () => resolvePrintChoice(false));
    }

    if (printChoiceModalEl) {
        printChoiceModalEl.addEventListener('hidden.bs.modal', () => {
            if (printChoiceResolver) {
                const resolver = printChoiceResolver;
                printChoiceResolver = null;
                resolver(false);
            }
        });
    }

    function saveNewCustomer() {
        if (!newCustomerFormEl) {
            notifyError('ไม่พบฟอร์มเพิ่มลูกค้า');
            return;
        }

        const textInputs = Array.from(newCustomerFormEl.querySelectorAll('input[type="text"]'));
        const firstName = textInputs[0] ? String(textInputs[0].value || '').trim() : '';
        const lastName = textInputs[1] ? String(textInputs[1].value || '').trim() : '';
        const nickname = textInputs[2] ? String(textInputs[2].value || '').trim() : '';
        const lineId = textInputs[3] ? String(textInputs[3].value || '').trim() : '';
        const phoneInput = newCustomerFormEl.querySelector('input[type="tel"]');
        const phone = phoneInput ? String(phoneInput.value || '').trim() : '';
        const fullName = `${firstName} ${lastName}`.trim();
        const customerName = fullName !== '' ? fullName : nickname;

        if (customerName === '' || phone === '') {
            notifyWarning('กรุณากรอกชื่อลูกค้าและเบอร์โทรให้ครบ');
            return;
        }

        fetch(quickCreateCustomerUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                name: customerName,
                phone: phone,
                line_id: lineId !== '' ? lineId : null,
            }),
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(extractErrorMessage(data, 'บันทึกข้อมูลลูกค้าไม่สำเร็จ'));
            }

            const customer = data.customer || null;
            if (customer && customer.id) {
                customers.push(customer);

                if (customerNameInputEl) {
                    customerNameInputEl.value = String(customer.name || '');
                }
                if (customerIdHiddenEl) {
                    customerIdHiddenEl.value = String(customer.id);
                }
                updateCustomerMatchHint(`ผูก CRM: ${customer.name || ''}`, 'success');
            }

            notifySuccess(data.message || 'บันทึกลูกค้าเรียบร้อยแล้ว');

            const modalEl = document.getElementById('newCustomerModal');
            const modal = (typeof bootstrap !== 'undefined' && bootstrap.Modal)
                ? bootstrap.Modal.getOrCreateInstance(modalEl)
                : null;
            if (modal) modal.hide();
            newCustomerFormEl.reset();
        })
        .catch((error) => {
            notifyError(error.message || 'บันทึกข้อมูลลูกค้าไม่สำเร็จ');
        });
    }

    applyBookingContext();
    renderCart();
</script>
@endpush

