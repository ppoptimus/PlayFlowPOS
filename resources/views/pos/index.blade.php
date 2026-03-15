@extends('layouts.main')

@section('title', 'POS | PlayFlow Spa POS')
@section('page_title', 'POS ขายบริการ')
@section('page_subtitle', 'Mockup: เพิ่มบริการ+สินค้า, ส่วนลด, วิธีชำระ, ผูกหมอนวด')

@section('content')
<div class="row g-3">
    <div class="col-12 col-xl-7">
        <section class="pf-card">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
                <h3 class="pf-section-title mb-0">รายการขาย</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm rounded-pill active tab-filter" data-filter="all">ทั้งหมด</button>
                    <button class="btn btn-sm rounded-pill tab-filter" data-filter="service">บริการนวด</button>
                    <button class="btn btn-sm rounded-pill tab-filter" data-filter="product">สินค้าขายปลีก</button>
                </div>
            </div>
            <input id="item-search" class="form-control rounded-pill mb-3" placeholder="ค้นหาเมนู / SKU">

            <div class="row g-2" id="item-grid">
                @foreach($items as $item)
                <div class="col-12 col-md-6 item-card-wrap" data-type="{{ $item['type'] }}" data-name="{{ strtolower($item['name']) }}">
                    <article class="item-card p-3 rounded-4 h-100" style="background:linear-gradient(120deg, {{ $item['color'] }}24, #ffffff); border:1px solid {{ $item['color'] }}44; cursor:pointer;"
                             data-id="{{ $item['id'] }}"
                             data-type="{{ $item['type'] }}"
                             data-name="{{ $item['name'] }}"
                             data-price="{{ $item['price'] }}"
                             data-duration="{{ $item['duration'] }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small text-secondary">{{ $item['id'] }}</div>
                                <div class="fw-semibold">{{ $item['name'] }}</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light">{{ $item['type'] === 'service' ? 'บริการ' : 'สินค้า' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div class="small text-secondary">
                                @if($item['duration'])
                                {{ $item['duration'] }} นาที
                                @else
                                Retail
                                @endif
                            </div>
                            <div class="fw-bold text-primary">{{ number_format($item['price']) }} ฿</div>
                        </div>
                    </article>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-5">
        <section class="pf-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="pf-section-title mb-0">บิลปัจจุบัน</h3>
                <span class="pf-badge">Mock</span>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-12 col-md-6">
                    <label class="small fw-semibold text-secondary mb-1">ลูกค้า</label>
                    <select id="customer-select" class="form-select">
                        @foreach($customers as $customer)
                        <option value="{{ $customer['id'] }}" data-points="{{ $customer['points'] }}">
                            {{ $customer['name'] }} ({{ $customer['phone'] }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="small fw-semibold text-secondary mb-1">หมอนวด</label>
                    <select id="masseuse-select" class="form-select">
                        @foreach($staff as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }} - {{ $s['role'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive mb-2">
                <table class="table align-middle table-sm">
                    <thead>
                        <tr>
                            <th>รายการ</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">ราคา</th>
                            <th class="text-end">รวม</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr><td colspan="5" class="text-center text-secondary py-3">ยังไม่มีรายการในบิล</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-semibold text-secondary mb-1">ส่วนลด</label>
                    <select id="discount-type" class="form-select">
                        <option value="none">ไม่ใช้ส่วนลด</option>
                        <option value="percent">เปอร์เซ็นต์ (%)</option>
                        <option value="fixed">จำนวนเงิน (฿)</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="small fw-semibold text-secondary mb-1">มูลค่าส่วนลด</label>
                    <input type="number" id="discount-value" class="form-control" min="0" value="0">
                </div>
            </div>

            <div class="mb-3">
                <label class="small fw-semibold text-secondary mb-1 d-block">วิธีชำระเงิน</label>
                <div class="d-flex gap-2 flex-wrap" id="payment-methods">
                    <button type="button" class="btn btn-outline-primary rounded-pill payment-btn active" data-payment="cash">เงินสด</button>
                    <button type="button" class="btn btn-outline-primary rounded-pill payment-btn" data-payment="promptpay">โอนเงิน/QR PromptPay</button>
                    <button type="button" class="btn btn-outline-primary rounded-pill payment-btn" data-payment="card">บัตรเครดิต</button>
                </div>
            </div>

            <div class="rounded-4 p-3" style="background:linear-gradient(120deg, rgba(49,184,233,.14), rgba(28,201,182,.14)); border:1px solid rgba(49,184,233,.22);">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="subtotal">0 ฿</strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><strong id="discount-amount">0 ฿</strong></div>
                <div class="d-flex justify-content-between fs-5 mt-1"><span>ยอดชำระสุทธิ</span><strong id="grand-total" class="text-primary">0 ฿</strong></div>
                <div class="small text-secondary mt-1">ผูกบิลกับหมอนวด: <span id="bill-masseuse">-</span></div>
                <button class="btn btn-lg w-100 mt-3 text-white fw-semibold" style="background:linear-gradient(120deg,#31b8e9,#1cc9b6); border:none;">
                    🔥 ชำระเงิน (Mock)
                </button>
            </div>
        </section>
    </div>
</div>
@endsection

@push('head')
<style>
    .tab-filter {
        border: 1px solid rgba(49, 184, 233, 0.25);
        background: rgba(255, 255, 255, 0.8);
        color: #2b648c;
        font-weight: 500;
    }
    .tab-filter.active {
        color: #fff;
        background: linear-gradient(120deg, #31b8e9, #1cc9b6);
        border-color: transparent;
    }
    .payment-btn.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(120deg, #31b8e9, #1cc9b6);
    }
    .qty-btn {
        width: 26px;
        height: 26px;
        border: 1px solid rgba(49, 184, 233, 0.4);
        border-radius: 50%;
        background: #fff;
        color: #2f78a4;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const items = @json($items);
        const cart = new Map();
        let activeFilter = "all";
        let paymentMethod = "cash";

        const itemGrid = document.getElementById("item-grid");
        const itemSearch = document.getElementById("item-search");
        const cartBody = document.getElementById("cart-body");
        const discountType = document.getElementById("discount-type");
        const discountValue = document.getElementById("discount-value");
        const subtotalEl = document.getElementById("subtotal");
        const discountEl = document.getElementById("discount-amount");
        const grandTotalEl = document.getElementById("grand-total");
        const masseuseSelect = document.getElementById("masseuse-select");
        const billMasseuse = document.getElementById("bill-masseuse");

        function formatCurrency(value) {
            return `${Number(value).toLocaleString()} ฿`;
        }

        function getDiscountAmount(subtotal) {
            const type = discountType.value;
            const value = Math.max(Number(discountValue.value || 0), 0);
            if (type === "percent") {
                return Math.min((subtotal * value) / 100, subtotal);
            }
            if (type === "fixed") {
                return Math.min(value, subtotal);
            }
            return 0;
        }

        function renderCart() {
            if (cart.size === 0) {
                cartBody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-3">ยังไม่มีรายการในบิล</td></tr>';
            } else {
                const rows = [];
                cart.forEach((line) => {
                    const lineTotal = line.price * line.qty;
                    rows.push(`
                        <tr>
                            <td>
                                <div class="fw-semibold">${line.name}</div>
                                <div class="small text-secondary">${line.type === "service" ? "บริการ" : "สินค้า"}</div>
                            </td>
                            <td class="text-center">
                                <button class="qty-btn" data-action="dec" data-id="${line.id}">-</button>
                                <span class="mx-2 fw-semibold">${line.qty}</span>
                                <button class="qty-btn" data-action="inc" data-id="${line.id}">+</button>
                            </td>
                            <td class="text-end">${formatCurrency(line.price)}</td>
                            <td class="text-end fw-semibold">${formatCurrency(lineTotal)}</td>
                            <td class="text-end"><button class="btn btn-sm btn-link text-danger p-0" data-action="remove" data-id="${line.id}">ลบ</button></td>
                        </tr>
                    `);
                });
                cartBody.innerHTML = rows.join("");
            }

            const subtotal = [...cart.values()].reduce((sum, line) => sum + (line.price * line.qty), 0);
            const discount = getDiscountAmount(subtotal);
            const total = Math.max(subtotal - discount, 0);
            subtotalEl.textContent = formatCurrency(subtotal);
            discountEl.textContent = `- ${formatCurrency(discount)}`;
            grandTotalEl.textContent = formatCurrency(total);
            billMasseuse.textContent = masseuseSelect.options[masseuseSelect.selectedIndex]?.text || "-";
        }

        function upsertItem(itemId) {
            const found = items.find((i) => i.id === itemId);
            if (!found) return;
            const current = cart.get(itemId);
            if (current) {
                current.qty += 1;
                cart.set(itemId, current);
            } else {
                cart.set(itemId, {
                    id: found.id,
                    type: found.type,
                    name: found.name,
                    price: Number(found.price),
                    qty: 1,
                });
            }
            renderCart();
        }

        function applyFilter() {
            const keyword = itemSearch.value.trim().toLowerCase();
            [...document.querySelectorAll(".item-card-wrap")].forEach((cardWrap) => {
                const typeOk = activeFilter === "all" || cardWrap.dataset.type === activeFilter;
                const nameOk = cardWrap.dataset.name.includes(keyword);
                cardWrap.style.display = typeOk && nameOk ? "" : "none";
            });
        }

        itemGrid.addEventListener("click", (e) => {
            const itemCard = e.target.closest(".item-card");
            if (itemCard) {
                upsertItem(itemCard.dataset.id);
            }
        });

        cartBody.addEventListener("click", (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;
            if (!action || !id || !cart.has(id)) return;

            const line = cart.get(id);
            if (action === "inc") line.qty += 1;
            if (action === "dec") line.qty -= 1;
            if (action === "remove" || line.qty <= 0) {
                cart.delete(id);
            } else {
                cart.set(id, line);
            }
            renderCart();
        });

        [...document.querySelectorAll(".tab-filter")].forEach((btn) => {
            btn.addEventListener("click", () => {
                activeFilter = btn.dataset.filter;
                document.querySelectorAll(".tab-filter").forEach((x) => x.classList.remove("active"));
                btn.classList.add("active");
                applyFilter();
            });
        });

        itemSearch.addEventListener("input", applyFilter);
        discountType.addEventListener("change", renderCart);
        discountValue.addEventListener("input", renderCart);
        masseuseSelect.addEventListener("change", renderCart);

        [...document.querySelectorAll(".payment-btn")].forEach((btn) => {
            btn.addEventListener("click", () => {
                paymentMethod = btn.dataset.payment;
                document.querySelectorAll(".payment-btn").forEach((x) => x.classList.remove("active"));
                btn.classList.add("active");
                btn.closest("#payment-methods").dataset.selectedPayment = paymentMethod;
            });
        });

        applyFilter();
        renderCart();
    })();
</script>
@endpush
