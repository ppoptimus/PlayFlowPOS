@php
    $mobileSidebarRole = (string) (auth()->user()->role ?? '');
    $canManageMembershipLevels = in_array($mobileSidebarRole, ['super_admin', 'branch_manager'], true);

    $mobileBackofficeMenus = [
        [
            'route' => 'branches.index',
            'icon' => 'bi-shop-window',
            'title' => 'ข้อมูลร้าน / สาขา',
            'subtitle' => 'ตั้งค่าข้อมูลสาขาและร้าน',
        ],
        [
            'route' => 'services.index',
            'icon' => 'bi-list-stars',
            'title' => 'บริการ',
            'subtitle' => 'จัดการบริการและราคา',
        ],
        [
            'route' => 'masseuse',
            'icon' => 'bi-person-badge',
            'title' => 'หมอนวด',
            'subtitle' => 'จัดการข้อมูลหมอนวด',
        ],
        [
            'route' => 'massage-rooms',
            'icon' => 'bi-door-open',
            'title' => 'ห้องนวด',
            'subtitle' => 'จัดการเตียงและห้องนวด',
        ],
        [
            'route' => 'admin.commission.index',
            'icon' => 'bi-percent',
            'title' => 'ตั้งค่าคอมมิชชั่น',
            'subtitle' => 'จัดการค่าคอมมิชชั่น',
        ],
        [
            'route' => 'products',
            'icon' => 'bi-box-seam-fill',
            'title' => 'สินค้า & สต็อก',
            'subtitle' => 'จัดการสินค้าและสต็อก',
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
            'route' => 'reports',
            'icon' => 'bi-bar-chart-line-fill',
            'title' => 'รายงานวิเคราะห์',
            'subtitle' => 'ยอดขาย บริการ หมอนวด สินค้า',
        ],
        [
            'route' => 'staff.index',
            'icon' => 'bi-person-badge',
            'title' => 'พนักงาน',
            'subtitle' => 'จัดการข้อมูลพนักงาน',
        ],
        [
            'route' => 'users.index',
            'icon' => 'bi-shield-check',
            'title' => 'ผู้ใช้งานระบบ',
            'subtitle' => 'จัดการบัญชีและสิทธิ์เข้าใช้',
        ],
    ];

    if ($canManageMembershipLevels) {
        array_splice($mobileBackofficeMenus, 7, 0, [[
            'route' => 'membership-levels',
            'icon' => 'bi-sliders',
            'title' => 'สมาชิก',
            'subtitle' => 'จัดการระดับสมาชิก',
        ], [
            'route' => 'packages',
            'icon' => 'bi-box2-heart',
            'title' => 'แพ็กเกจ',
            'subtitle' => 'จัดการแพ็กเกจและโปรโมชั่น',
        ]]);
    }
@endphp

<div class="mobile-menu-panel">
    <p class="mobile-menu-heading mb-3">เมนูหลัก</p>
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

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="mobile-menu-link mobile-menu-link--logout w-100 text-start">
                <span class="mobile-menu-icon"><i class="bi bi-box-arrow-right"></i></span>
                <span class="mobile-menu-content">
                    <span class="mobile-menu-title">ออกจากระบบ</span>
                    <span class="mobile-menu-subtitle">ออกจากบัญชีผู้ใช้งานปัจจุบัน</span>
                </span>
            </button>
        </form>
    </div>
</div>
