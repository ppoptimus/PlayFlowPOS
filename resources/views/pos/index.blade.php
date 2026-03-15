@extends('layouts.main')

@section('title', 'POS - PlayFlow POS')
@section('page_title', 'หน้าจอขายหน้าร้าน (POS)')
@section('page_subtitle', 'สาขา: สุขุมวิท | พนักงาน: Manager')

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="mb-3">
                    <label class="form-label fw-bold small">ค้นหา</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="item-search" class="form-control border-start-0" placeholder="ชื่อ/SKU...">
                    </div>
                </div>
                
                <label class="form-label fw-bold small">หมวดหมู่</label>
                <div class="list-group list-group-flush gap-2 border-0" id="category-filter">
                    <button class="list-group-item list-group-item-action rounded-3 border-0 active tab-filter" data-filter="all">
                        <i class="bi bi-grid-fill me-2"></i> ทั้งหมด
                    </button>
                    <button class="list-group-item list-group-item-action rounded-3 border-0 tab-filter" data-filter="service">
                        <i class="bi bi-person-walking me-2"></i> บริการนวด
                    </button>
                    <button class="list-group-item list-group-item-action rounded-3 border-0 tab-filter" data-filter="product">
                        <i class="bi bi-box-seam me-2"></i> สินค้าปลีก
                    </button>
                </div>
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
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
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
</script>
@endpush