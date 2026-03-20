@php
    $footerRole = (string) (auth()->user()->role ?? '');
    $isMasseuseFooter = $footerRole === 'masseuse';
@endphp

<nav class="pf-mobile-nav" aria-label="เมนูล่างมือถือ">
    @if($isMasseuseFooter)
        <a href="{{ route('masseuse.self') }}"
           class="pf-mobile-nav-item {{ request()->routeIs('masseuse.self') || request()->routeIs('masseuse') ? 'is-active' : '' }}">
            <i class="bi bi-wallet2"></i>
            <span>ค่ามือฉัน</span>
        </a>
    @else
        <a href="{{ route('dashboard') }}"
           class="pf-mobile-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>หน้าหลัก</span>
        </a>
        <a href="{{ route('pos') }}"
           class="pf-mobile-nav-item {{ request()->routeIs('pos*') ? 'is-active' : '' }}">
            <i class="bi bi-cart4"></i>
            <span>ขาย</span>
        </a>
        <a href="{{ route('booking') }}"
           class="pf-mobile-nav-item {{ request()->routeIs('booking*') ? 'is-active' : '' }}">
            <i class="bi bi-calendar-check"></i>
            <span>คิว</span>
        </a>
        <a href="{{ route('masseuse') }}"
           class="pf-mobile-nav-item {{ request()->routeIs('masseuse*') ? 'is-active' : '' }}">
            <i class="bi bi-people"></i>
            <span>หมอนวด</span>
        </a>
    @endif
</nav>
