@extends('layouts.main')

@section('title', 'POS - PlayFlow POS')
@section('page_title', 'หน้าจอขาย')
@section('page_subtitle', 'สุขุมวิท | Manager')

@section('content')
<div class="row g-3">
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
                             style="cursor: pointer;"
                             onclick="addToCart('{{ $item['id'] }}', '{{ $item['name'] }}', {{ $item['price'] }}, '{{ $item['type'] }}')">
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
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="small fw-bold text-muted">ลูกค้า (CRM)</label>
                        <select id="customer-select" class="form-select form-select-sm rounded-3">
                            @foreach($customers as $customer)
                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
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

                    <button class="btn btn-primary w-100 btn-lg rounded-pill mt-3 py-3 fw-bold shadow-sm" onclick="checkout()">
                        <i class="bi bi-check-circle-fill me-2"></i> ชำระเงิน (Mock)
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
@endsection

@push('scripts')
<script>
    let cart = [];

    function addToCart(id, name, price, type) {
        const index = cart.findIndex(item => item.id === id);
        if (index > -1) {
            cart[index].qty++;
        } else {
            cart.push({ id, name, price, type, qty: 1 });
        }
        renderCart();
    }

    function remove(id) {
        cart = cart.filter(item => item.id !== id);
        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty += delta;
            if (item.qty <= 0) remove(id);
            else renderCart();
        }
    }

    function renderCart() {
        const cartList = document.getElementById('cart-list');
        const emptyMsg = document.getElementById('empty-cart-msg');
        
        if (cart.length === 0) {
            cartList.innerHTML = '<div class="text-center text-muted py-5" id="empty-cart-msg">ยังไม่มีรายการในบิล</div>';
            calculate();
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
    }

    function calculate() {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discount-input').value) || 0;
        const total = Math.max(0, subtotal - discount);

        document.getElementById('subtotal').innerText = subtotal.toLocaleString() + ' ฿';
        document.getElementById('grand-total').innerText = total.toLocaleString() + ' ฿';
    }

    // ฟิลเตอร์หมวดหมู่
    document.querySelectorAll('.tab-filter').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.tab-filter').forEach(b => {
                b.classList.remove('active', 'bg-primary-subtle');
            });
            btn.classList.add('active', 'bg-primary-subtle');
            
            // อัปเดตข้อความบนปุ่ม dropdown ให้ตรงกับที่เลือก
            const iconHtml = btn.querySelector('i').outerHTML;
            const textHtml = btn.textContent.trim();
            document.getElementById('categoryDropdownBtn').innerHTML = `<span class="d-flex align-items-center">${iconHtml} <span class="ms-1">${textHtml}</span></span> <i class="bi bi-chevron-down text-muted"></i>`;
            
            const filter = btn.dataset.filter;
            document.querySelectorAll('.item-card-wrap').forEach(item => {
                item.style.display = (filter === 'all' || item.dataset.type === filter) ? 'block' : 'none';
            });
        });
    });

    // ค้นหา
    document.getElementById('item-search').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.item-card-wrap').forEach(item => {
            const name = item.dataset.name;
            item.style.display = name.includes(term) ? 'block' : 'none';
        });
    });

    // ปุ่มเลือกวิธีชำระเงิน
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-secondary');
        });
    });

    document.getElementById('discount-input').addEventListener('input', calculate);

    function checkout() {
        if (cart.length === 0) return alert('กรุณาเลือกรายการสินค้า');
        alert('บันทึกรายการสำเร็จ (จำลองการทำงาน)');
        cart = [];
        renderCart();
    }

    function saveNewCustomer() {
        alert('บันทึกข้อมูลลูกค้าสำเร็จ (จำลองการทำงาน)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('newCustomerModal'));
        if (modal) modal.hide();
        document.getElementById('new-customer-form').reset();
    }
</script>
@endpush