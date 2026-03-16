<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button class="btn mobile-menu-btn d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="เปิดเมนูหลัก">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div>
                <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'PlayFlow System')</h5>
                <small class="text-muted">@yield('page_subtitle', 'Branch: Sukhumvit')</small>
            </div>
        </div>

        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle bg-light p-2 rounded-pill px-3" id="userDropdown" data-bs-toggle="dropdown">
                    <img src="https://i.pravatar.cc/100?u=manager" alt="user" width="32" height="32" class="rounded-circle me-2 border border-primary">
                    <span class="d-none d-md-inline small fw-bold">Manager Sukhumvit</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>ตั้งค่า</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
