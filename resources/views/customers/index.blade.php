@extends('layouts.main')

@section('title', 'ลูกค้า (CRM) - PlayFlow')
@section('page_title', 'ลูกค้า (Customer CRM)')

@push('head')
<style>
    .customers-card-header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }

    .customers-toolbar {
        min-height: 52px;
        align-items: center !important;
    }

    .customers-toolbar h6 {
        display: inline-flex;
        align-items: center;
        margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
        .customers-toolbar {
            flex-direction: row;
            flex-wrap: nowrap !important;
            justify-content: space-between !important;
            align-items: center !important;
            text-align: left;
            gap: 0.5rem !important;
            min-height: 44px;
        }

        .customers-card-header {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .customers-toolbar h6 {
            font-size: 1.12rem;
        }

        .customers-toolbar .btn {
            min-width: 0;
            white-space: nowrap;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .customers-table thead th:nth-child(2),
        .customers-table thead th:nth-child(3),
        .customers-table thead th:nth-child(4),
        .customers-table thead th:nth-child(5),
        .customers-table tbody td:nth-child(2),
        .customers-table tbody td:nth-child(3),
        .customers-table tbody td:nth-child(4),
        .customers-table tbody td:nth-child(5) {
            display: none !important;
        }

        .customers-table th,
        .customers-table td {
            vertical-align: middle;
            padding-left: 0.6rem;
            padding-right: 0.6rem;
        }

        .customers-table thead th:first-child,
        .customers-table thead th:last-child,
        .customers-table tbody td:first-child,
        .customers-table tbody td:last-child {
            text-align: center !important;
        }

        .customers-table tbody td:first-child {
            max-width: 180px;
            white-space: normal;
            line-height: 1.25;
        }

        .customers-action-group {
            justify-content: center !important;
            flex-wrap: wrap;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="row g-3">
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

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-3 pb-0 customers-card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 customers-toolbar">
                    <h6 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>ฐานข้อมูลลูกค้า CRM</h6>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="open-add-customer-btn">
                        <i class="bi bi-person-plus-fill me-1"></i> เพิ่มลูกค้าใหม่
                    </button>
                </div>
            </div>
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-lg-5">
                        <form method="GET" action="{{ route('customers') }}">
                            <label class="form-label small fw-bold">ค้นหาลูกค้า</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       name="search"
                                       value="{{ $search }}"
                                       placeholder="ชื่อ, เบอร์โทร, LINE ID">
                            </div>
                        </form>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="rounded-3 border p-2 bg-light h-100">
                            <div class="text-muted small">ลูกค้าทั้งหมด</div>
                            <div class="fw-bold fs-5">{{ number_format($summary['total_customers'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="rounded-3 border p-2 bg-light h-100">
                            <div class="text-muted small">ลูกค้า Active 30 วัน</div>
                            <div class="fw-bold fs-5">{{ number_format($summary['active_customers_30d'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 customers-table">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">ชื่อลูกค้า</th>
                                <th>เบอร์โทร</th>
                                <th>LINE ID</th>
                                <th>จำนวนครั้งใช้บริการ</th>
                                <th>เข้าล่าสุด</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            @php
                                $customerPayload = [
                                    'id' => $customer['id'],
                                    'name' => $customer['name'],
                                    'phone' => $customer['phone'],
                                    'line_id' => $customer['line_id'],
                                    'preferred_pressure_level' => $customer['preferred_pressure_level'],
                                    'health_notes' => $customer['health_notes'],
                                    'contraindications' => $customer['contraindications'],
                                ];
                            @endphp
                            <tr>
                                <td class="px-3 fw-semibold">{{ $customer['name'] }}</td>
                                <td>{{ $customer['phone'] !== '' ? $customer['phone'] : '-' }}</td>
                                <td>{{ $customer['line_id'] !== '' ? $customer['line_id'] : '-' }}</td>
                                <td>{{ number_format($customer['visit_count']) }} ครั้ง</td>
                                <td>{{ $customer['last_visit_at'] ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2 customers-action-group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 edit-customer-btn"
                                                data-update-url="{{ route('customers.update', ['customerId' => $customer['id']]) }}"
                                                data-customer='@json($customerPayload)'>
                                            แก้ไข
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info rounded-pill px-3 history-customer-btn"
                                                data-customer-id="{{ $customer['id'] }}"
                                                data-customer-name="{{ $customer['name'] }}">
                                            ดูประวัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลลูกค้า</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="add_customer">
                <div class="modal-body pt-1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ชื่อลูกค้า <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">LINE ID</label>
                            <input type="text" name="line_id" class="form-control rounded-3" value="{{ old('line_id') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">น้ำหนักมือที่ชอบ</label>
                            <select name="preferred_pressure_level" class="form-select rounded-3">
                                <option value="">ไม่ระบุ</option>
                                @foreach($pressureLevels as $option)
                                <option value="{{ $option['value'] }}" {{ old('preferred_pressure_level') === $option['value'] ? 'selected' : '' }}>
                                    {{ $option['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ประวัติสุขภาพ</label>
                            <textarea name="health_notes" class="form-control rounded-3" rows="3">{{ old('health_notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">จุดที่ต้องระวัง / ห้ามนวด</label>
                            <textarea name="contraindications" class="form-control rounded-3" rows="3">{{ old('contraindications') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> บันทึกลูกค้า
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>แก้ไขข้อมูลลูกค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="edit-customer-form" action="{{ route('customers.update', ['customerId' => 0]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit_customer">
                <input type="hidden" name="edit_customer_id" id="edit-customer-id" value="{{ old('edit_customer_id') }}">
                <div class="modal-body pt-1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ชื่อลูกค้า <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit-customer-name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="edit-customer-phone" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">LINE ID</label>
                            <input type="text" name="line_id" id="edit-customer-line-id" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">น้ำหนักมือที่ชอบ</label>
                            <select name="preferred_pressure_level" id="edit-customer-pressure" class="form-select rounded-3">
                                <option value="">ไม่ระบุ</option>
                                @foreach($pressureLevels as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ประวัติสุขภาพ</label>
                            <textarea name="health_notes" id="edit-customer-health-notes" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">จุดที่ต้องระวัง / ห้ามนวด</label>
                            <textarea name="contraindications" id="edit-customer-contraindications" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="historyCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i><span id="history-modal-title">ประวัติการใช้บริการ</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1" id="history-modal-body">
                <div class="text-center text-muted py-4">กำลังโหลดข้อมูล...</div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const addCustomerModalEl = document.getElementById('addCustomerModal');
    const editCustomerModalEl = document.getElementById('editCustomerModal');
    const historyCustomerModalEl = document.getElementById('historyCustomerModal');
    const historyModalTitleEl = document.getElementById('history-modal-title');
    const historyModalBodyEl = document.getElementById('history-modal-body');
    const editCustomerFormEl = document.getElementById('edit-customer-form');
    const openAddCustomerBtn = document.getElementById('open-add-customer-btn');
    const editUpdateUrlTemplate = @json(route('customers.update', ['customerId' => '__ID__']));
    const historyUrlTemplate = @json(route('customers.history', ['customerId' => '__ID__']));
    const oldForm = @json(old('_form'));
    const oldEditCustomerId = @json(old('edit_customer_id'));

    function resolveTemplateUrl(template, customerId) {
        return String(template || '').replace('__ID__', String(customerId || ''));
    }

    function showModal(modalEl) {
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
    }

    function setEditFormCustomer(customer, updateUrl) {
        if (!editCustomerFormEl || !customer) return;

        editCustomerFormEl.action = updateUrl;
        const customerIdInput = document.getElementById('edit-customer-id');
        const nameInput = document.getElementById('edit-customer-name');
        const phoneInput = document.getElementById('edit-customer-phone');
        const lineIdInput = document.getElementById('edit-customer-line-id');
        const pressureSelect = document.getElementById('edit-customer-pressure');
        const healthNotesInput = document.getElementById('edit-customer-health-notes');
        const contraindicationsInput = document.getElementById('edit-customer-contraindications');

        if (customerIdInput) customerIdInput.value = String(customer.id || '');
        if (nameInput) nameInput.value = String(customer.name || '');
        if (phoneInput) phoneInput.value = String(customer.phone || '');
        if (lineIdInput) lineIdInput.value = String(customer.line_id || '');
        if (pressureSelect) pressureSelect.value = customer.preferred_pressure_level || '';
        if (healthNotesInput) healthNotesInput.value = String(customer.health_notes || '');
        if (contraindicationsInput) contraindicationsInput.value = String(customer.contraindications || '');
    }

    function renderHistoryTable(customer, history) {
        const rows = (history || []).map((item) => `
            <tr>
                <td class="px-3 fw-semibold">${item.order_no || '-'}</td>
                <td>${item.created_at || '-'}</td>
                <td>
                    <div class="small">${item.item_summary || '-'}</div>
                    <div class="text-muted small">${Number(item.item_count || 0).toLocaleString('th-TH')} รายการ</div>
                </td>
                <td>${item.payment_method_label || '-'}</td>
                <td><span class="badge ${item.status === 'paid' ? 'text-bg-success' : 'text-bg-secondary'}">${item.status_label || '-'}</span></td>
                <td class="text-end fw-semibold">${Number(item.grand_total || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ฿</td>
            </tr>
        `).join('');

        const summary = `
            <div class="d-flex flex-wrap gap-3 border rounded-3 p-3 mb-3 bg-light">
                <div><span class="text-muted">ลูกค้า:</span> <span class="fw-semibold">${customer.name || '-'}</span></div>
                <div><span class="text-muted">เบอร์:</span> <span class="fw-semibold">${customer.phone || '-'}</span></div>
                <div><span class="text-muted">ใช้บริการทั้งหมด:</span> <span class="fw-semibold">${Number(customer.visit_count || 0).toLocaleString('th-TH')} ครั้ง</span></div>
                <div><span class="text-muted">ยอดสะสม:</span> <span class="fw-semibold">${Number(customer.total_spent || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ฿</span></div>
            </div>
        `;

        if (!rows) {
            return `${summary}<div class="text-center text-muted py-4">ยังไม่มีประวัติการใช้บริการของลูกค้ารายนี้</div>`;
        }

        return `
            ${summary}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">เลขที่บิล</th>
                            <th>วันเวลา</th>
                            <th>รายการ</th>
                            <th>ชำระ</th>
                            <th>สถานะ</th>
                            <th class="text-end">ยอดสุทธิ</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    async function openHistoryModal(customerId, customerName) {
        if (!historyModalBodyEl || !historyModalTitleEl) return;

        historyModalTitleEl.textContent = `ประวัติการใช้บริการ: ${customerName || '-'}`;
        historyModalBodyEl.innerHTML = '<div class="text-center text-muted py-4">กำลังโหลดข้อมูล...</div>';
        showModal(historyCustomerModalEl);

        try {
            const response = await fetch(resolveTemplateUrl(historyUrlTemplate, customerId), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.message || 'โหลดประวัติลูกค้าไม่สำเร็จ');
            }

            historyModalBodyEl.innerHTML = renderHistoryTable(payload.customer || {}, payload.history || []);
        } catch (error) {
            historyModalBodyEl.innerHTML = `<div class="text-center text-danger py-4">${error.message}</div>`;
        }
    }

    if (openAddCustomerBtn) {
        openAddCustomerBtn.addEventListener('click', () => showModal(addCustomerModalEl));
    }

    document.querySelectorAll('.edit-customer-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const updateUrl = button.dataset.updateUrl || '';
            const rawCustomer = button.dataset.customer || '{}';
            const customer = JSON.parse(rawCustomer);
            setEditFormCustomer(customer, updateUrl);
            showModal(editCustomerModalEl);
        });
    });

    document.querySelectorAll('.history-customer-btn').forEach((button) => {
        button.addEventListener('click', () => {
            openHistoryModal(
                Number(button.dataset.customerId || 0),
                String(button.dataset.customerName || '')
            );
        });
    });

    if (oldForm === 'add_customer') {
        showModal(addCustomerModalEl);
    }

    if (oldForm === 'edit_customer' && oldEditCustomerId) {
        setEditFormCustomer({
            id: oldEditCustomerId,
            name: @json(old('name')),
            phone: @json(old('phone')),
            line_id: @json(old('line_id')),
            preferred_pressure_level: @json(old('preferred_pressure_level')),
            health_notes: @json(old('health_notes')),
            contraindications: @json(old('contraindications')),
        }, resolveTemplateUrl(editUpdateUrlTemplate, oldEditCustomerId));
        showModal(editCustomerModalEl);
    }
</script>
@endpush
