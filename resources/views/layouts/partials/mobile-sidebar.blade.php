@php
    $mobileSidebarRole = (string) (auth()->user()->role ?? '');
    $canManageMembershipLevels = in_array($mobileSidebarRole, ['admin', 'super_admin'], true);

    $mobileBackofficeMenus = [
        [
            'route' => 'branches',
            'icon' => 'bi-shop-window',
            'title' => 'ข้อมูลร้าน',
            'subtitle' => 'ตั้งค่าข้อมูลสาขาและร้าน',
        ],
        [
            'route' => 'products',
            'icon' => 'bi-list-stars',
            'title' => 'บริการ',
            'subtitle' => 'จัดการบริการและราคา',
        ],
        [
            'route' => 'masseuse',
            'icon' => 'bi-person-badge',
            'title' => 'หมอนวด',
            'subtitle' => 'จัดการข้อมูลหมอนวด ค่ามือ และค่าคอม',
        ],
        [
            'route' => 'booking',
            'icon' => 'bi-door-open',
            'title' => 'ห้องนวด',
            'subtitle' => 'จัดการเตียงและห้องนวด',
        ],
        [
            'route' => 'promotions',
            'icon' => 'bi-megaphone',
            'title' => 'แพ็กเกจโปรโมชั่น',
            'subtitle' => 'ตั้งค่าโปรและแพ็กเกจขาย',
        ],
        [
            'route' => 'customers',
            'icon' => 'bi-people',
            'title' => 'ลูกค้า',
            'subtitle' => 'แก้ไขข้อมูลลูกค้า',
        ],
        [
            'route' => 'receipts',
            'icon' => 'bi-receipt-cutoff',
            'title' => 'ใบเสร็จ',
            'subtitle' => 'ตรวจสอบย้อนหลังและพิมพ์บิล',
        ],
        [
            'route' => 'users',
            'icon' => 'bi-shield-check',
            'title' => 'สิทธิ์เข้าใช้',
            'subtitle' => 'จัดการสิทธิ์ผู้ใช้งาน',
        ],
    ];

    if ($canManageMembershipLevels) {
        array_splice($mobileBackofficeMenus, 6, 0, [[
            'route' => 'membership-levels',
            'icon' => 'bi-sliders',
            'title' => 'สมาชิก',
            'subtitle' => 'จัดการระดับสมาชิก',
        ]]);
    }
@endphp

<div class="mobile-menu-panel">
    <p class="mobile-menu-heading mb-3">เมนูหลังบ้าน</p>
    <div class="d-flex flex-column gap-2">
        @foreach($mobileBackofficeMenus as $item)
        <a href="{{ route($item['route']) }}" class="mobile-menu-link {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
            <span class="mobile-menu-icon"><i class="bi {{ $item['icon'] }}"></i></span>
            <span class="mobile-menu-content">
                <span class="mobile-menu-title">{{ $item['title'] }}</span>
                <span class="mobile-menu-subtitle">{{ $item['subtitle'] }}</span>
            </span>
            <i class="bi bi-chevron-right mobile-menu-arrow"></i>
        </a>
        @endforeach
    </div>
</div>
