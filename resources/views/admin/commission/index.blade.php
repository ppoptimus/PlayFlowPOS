@extends('layouts.main')

@section('title', 'ตั้งค่าคอมมิชชัน')
@section('page_title', 'ตั้งค่าคอมมิชชัน')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">จัดการค่าคอมมิชชัน</h1>
            <p class="text-muted">รายการสินค้าและบริการที่มีการคิดค่าตอบแทนให้พนักงาน</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addCommissionModal">
            <i class="fas fa-plus mr-2"></i>เพิ่มค่าคอมมิชชันใหม่
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover">
                <thead class="thead-light text-uppercase">
                    <tr>
                        <th>ประเภท</th>
                        <th>ชื่อรายการ</th>
                        <th>รูปแบบ</th>
                        <th>ค่าตอบแทน</th>
                        <th>ต้นทุนหักออก</th>
                        <th class="text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($configs as $config)
                    <tr>
                        <td><span class="badge badge-secondary px-3">{{ strtoupper($config->item_type) }}</span></td>
                        <td class="font-weight-bold text-dark">{{ $config->item_name }}</td>
                        <td>{{ $config->type == 'fixed' ? 'เงินก้อน' : 'เปอร์เซ็นต์' }}</td>
                        <td>{{ number_format($config->value, 2) }}{{ $config->type == 'percent' ? '%' : ' ฿' }}</td>
                        <td>{{ number_format($config->deduct_cost, 2) }} ฿</td>
                        <td class="text-right">
                            <form action="{{ url('admin/commission/delete/'.$config->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0"><i class="fas fa-trash"></i> ลบ</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">ยังไม่มีการตั้งค่าคอมมิชชัน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCommissionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content border-0">
            <form action="{{ route('admin.commission.update') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เลือกรายการที่ต้องการคิดค่าคอมมิชชัน</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>1. เลือกประเภทไอเทม</label>
                        <select name="item_type" id="item_type" class="form-control custom-select" required onchange="filterItems()">
                            <option value="">-- เลือกประเภท --</option>
                            <option value="service">บริการ (Service)</option>
                            <option value="product">สินค้า (Product)</option>
                            <option value="package">แพ็กเกจ (Package)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>2. เลือกชื่อรายการ</label>
                        <select name="item_id" id="item_id" class="form-control custom-select" required>
                            <option value="">-- เลือกรายการ --</option>
                        </select>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>รูปแบบการจ่าย</label>
                            <select name="type" class="form-control custom-select">
                                <option value="fixed">เงินก้อน (฿)</option>
                                <option value="percent">เปอร์เซ็นต์ (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>มูลค่าตอบแทน</label>
                            <input type="number" name="value" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>ต้นทุนหักออก (บาท)</label>
                            <input type="number" name="deduct_cost" class="form-control" placeholder="0.00">
                            <small class="text-muted">*กรณีคิดเป็น % จะหักยอดนี้ออกจากราคาขายก่อนคำนวณ</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">ยืนยันการเพิ่ม</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const data = {
        service: @json($availableServices),
        product: @json($availableProducts),
        package: @json($availablePackages)
    };

    function filterItems() {
        const type = document.getElementById('item_type').value;
        const select = document.getElementById('item_id');
        select.innerHTML = '<option value="">-- เลือกรายการ --</option>';
        if(type && data[type]) {
            data[type].forEach(item => {
                select.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        }
    }
</script>
@endpush
@endsection