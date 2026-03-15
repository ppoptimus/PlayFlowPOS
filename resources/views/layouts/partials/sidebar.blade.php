<style>
    .pf-sidebar .nav-link.link-dark { color: #2e3f55 !important; }
    .pf-sidebar .nav-link.link-dark:hover {
        color: #1f73e0 !important;
        background-color: rgba(31, 115, 224, 0.08) !important;
    }
    .pf-sidebar .text-muted { color: #5c728a !important; }
</style>

<div class="pf-sidebar">
<div class="mb-4 px-2">
    <h4 class="fw-bold text-primary mb-0"><i class="bi bi-flower1"></i> PlayFlow</h4>
    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Spa Management System</small>
</div>

<div class="nav flex-column nav-pills">
    <small class="text-muted fw-bold mb-2 px-2" style="font-size: 0.75rem;">งานหลัก</small>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'link-dark' }}">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a href="{{ route('pos') }}" class="nav-link {{ request()->routeIs('pos') ? 'active' : 'link-dark' }}">
        <i class="bi bi-cart-fill me-2"></i> POS คิดเงิน
    </a>
    <a href="{{ route('booking') }}" class="nav-link {{ request()->routeIs('booking') ? 'active' : 'link-dark' }}">
        <i class="bi bi-calendar-event-fill me-2"></i> ตารางคิว/จอง
    </a>

    <small class="text-muted fw-bold mt-4 mb-2 px-2" style="font-size: 0.75rem;">บริหารจัดการ</small>
    <a href="{{ route('customers') }}" class="nav-link link-dark"><i class="bi bi-people-fill me-2"></i> ลูกค้า (CRM)</a>
    <a href="{{ route('staff') }}" class="nav-link {{ request()->routeIs('staff') ? 'active' : 'link-dark' }}">
        <i class="bi bi-person-badge-fill me-2"></i> พนักงาน
    </a>
    <a href="{{ route('products') }}" class="nav-link link-dark"><i class="bi bi-box-seam-fill me-2"></i> สินค้า & สต็อก</a>
    <a href="{{ route('membership') }}" class="nav-link link-dark"><i class="bi bi-card-checklist me-2"></i> สมาชิก & แพ็กเกจ</a>

    <small class="text-muted fw-bold mt-4 mb-2 px-2" style="font-size: 0.75rem;">บัญชี & รายงาน</small>
    <a href="{{ route('reports') }}" class="nav-link link-dark"><i class="bi bi-bar-chart-line-fill me-2"></i> รายงานวิเคราะห์</a>
    <a href="{{ route('financial') }}" class="nav-link link-dark"><i class="bi bi-wallet2 me-2"></i> การเงิน/P&L</a>
    <a href="{{ route('commissions') }}" class="nav-link link-dark"><i class="bi bi-cash-stack me-2"></i> ค่าคอมมิชชัน</a>

    <small class="text-muted fw-bold mt-4 mb-2 px-2" style="font-size: 0.75rem;">ตั้งค่าระบบ</small>
    <a href="{{ route('branches') }}" class="nav-link link-dark"><i class="bi bi-building-fill me-2"></i> จัดการสาขา</a>
    <a href="{{ route('users') }}" class="nav-link link-dark"><i class="bi bi-shield-lock-fill me-2"></i> จัดการผู้ใช้งาน</a>
</div>

</div>
