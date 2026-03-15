<nav class="navbar fixed-bottom bg-white border-top d-lg-none py-1">
    <div class="container-fluid">
        <div class="d-flex w-100 justify-content-around text-center">
            <a href="{{ route('dashboard') }}" class="text-decoration-none {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}">
                <i class="bi bi-speedometer2 fs-4 d-block"></i>
                <span style="font-size: 10px;">หน้าหลัก</span>
            </a>
            <a href="{{ route('pos') }}" class="text-decoration-none {{ request()->routeIs('pos') ? 'text-primary' : 'text-muted' }}">
                <i class="bi bi-cart4 fs-4 d-block"></i>
                <span style="font-size: 10px;">ขาย</span>
            </a>
            <a href="{{ route('booking') }}" class="text-decoration-none {{ request()->routeIs('booking') ? 'active text-primary' : 'text-muted' }}">
                <i class="bi bi-calendar-check fs-4 d-block"></i>
                <span style="font-size: 10px;">คิว</span>
            </a>
            <a href="{{ route('staff') }}" class="text-decoration-none {{ request()->routeIs('staff') ? 'active text-primary' : 'text-muted' }}">
                <i class="bi bi-people fs-4 d-block"></i>
                <span style="font-size: 10px;">พนักงาน</span>
            </a>
        </div>
    </div>
</nav>