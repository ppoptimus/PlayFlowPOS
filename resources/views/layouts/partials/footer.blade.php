<nav class="pf-mobile-nav" aria-label="เมนูล่างมือถือ">
    <a href="{{ route('dashboard') }}"
       class="pf-mobile-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>หน้าหลัก</span>
    </a>
    <a href="{{ route('pos') }}"
       class="pf-mobile-nav-item {{ request()->routeIs('pos') ? 'is-active' : '' }}">
        <i class="bi bi-cart4"></i>
        <span>ขาย</span>
    </a>
    <a href="{{ route('booking') }}"
       class="pf-mobile-nav-item {{ request()->routeIs('booking') ? 'is-active' : '' }}">
        <i class="bi bi-calendar-check"></i>
        <span>คิว</span>
    </a>
    <a href="{{ route('staff') }}"
       class="pf-mobile-nav-item {{ request()->routeIs('staff') ? 'is-active' : '' }}">
        <i class="bi bi-people"></i>
        <span>พนักงาน</span>
    </a>
</nav>
