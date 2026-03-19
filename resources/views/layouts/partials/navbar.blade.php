@php
    $navbarProfile = $pfNavbarProfile ?? [
        'display_name' => optional(auth()->user())->username ?? 'User',
        'username' => optional(auth()->user())->username ?? '-',
        'avatar' => asset('img/icon.png'),
        'role_label' => 'ผู้ใช้งาน',
        'profile_url' => route('profile.show'),
    ];
@endphp

<style>
    .pf-navbar-profile-trigger {
        border: 0;
        background: transparent;
        padding: 0;
        display: inline-flex;
        align-items: center;
    }

    .pf-navbar-profile-modal .modal-content {
        border: 1px solid rgba(31, 115, 224, 0.16);
        border-radius: 1.2rem;
        overflow: hidden;
        box-shadow: 0 24px 50px rgba(14, 60, 120, 0.18);
    }

    .pf-navbar-profile-modal .modal-header {
        border-bottom: 0;
        color: #ffffff;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
    }

    .pf-navbar-profile-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .pf-navbar-profile-modal .modal-body {
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
        color: #25405c;
    }

    .pf-navbar-profile-modal .profile-modal-hero {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pf-navbar-profile-modal .profile-modal-avatar {
        width: 88px;
        height: 88px;
        border-radius: 1.25rem;
        object-fit: cover;
        border: 2px solid rgba(31, 115, 224, 0.14);
        box-shadow: 0 12px 24px rgba(17, 81, 146, 0.14);
        background: #ffffff;
        flex-shrink: 0;
    }

    .pf-navbar-profile-modal .profile-modal-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f456c;
        line-height: 1.15;
    }

    .pf-navbar-profile-modal .profile-modal-username {
        font-size: 0.92rem;
        color: #6883a0;
    }

    .pf-navbar-profile-modal .profile-modal-pill {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.72rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .pf-navbar-profile-modal .profile-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .pf-navbar-profile-modal .profile-modal-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #5d7a98;
        margin-bottom: 0.28rem;
    }

    .pf-navbar-profile-modal .profile-modal-value {
        min-height: 48px;
        display: flex;
        align-items: center;
        border-radius: 0.95rem;
        border: 1px solid rgba(31, 115, 224, 0.12);
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 0.78rem 0.92rem;
        color: #1f456c;
        font-weight: 600;
    }

    .pf-navbar-profile-modal .modal-footer {
        border-top: 1px solid rgba(31, 115, 224, 0.1);
        background: #ffffff;
    }

    @media (max-width: 575.98px) {
        .pf-navbar-profile-modal .profile-modal-hero {
            align-items: flex-start;
        }

        .pf-navbar-profile-modal .profile-modal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button
                class="btn mobile-menu-btn d-lg-none me-2"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar"
                aria-controls="mobileSidebar"
                aria-label="เปิดเมนูหลัก"
            >
                <span class="mobile-menu-btn-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <div>
                <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'PlayFlow System')</h5>
                <small class="text-muted">@yield('page_subtitle', 'Branch')</small>
            </div>
        </div>

        <div class="ms-auto d-flex align-items-center pf-navbar-actions">
            <button
                type="button"
                class="d-flex align-items-center link-dark text-decoration-none bg-light p-2 rounded-pill px-3 pf-navbar-profile-link"
                onclick="pfOpenNavbarProfileModal()"
            >
                <img
                    src="{{ $navbarProfile['avatar'] }}"
                    alt="{{ $navbarProfile['display_name'] }}"
                    width="38"
                    height="38"
                    class="rounded-circle me-2 border border-primary object-fit-cover"
                >
                <span class="d-none d-md-flex flex-column lh-sm text-start">
                    <span class="small fw-bold">{{ $navbarProfile['display_name'] }}</span>
                    <span class="opacity-75" style="font-size: 0.72rem;">{{ $navbarProfile['role_label'] }}</span>
                </span>
            </button>
        </div>
    </div>
</nav>

<div class="modal fade pf-navbar-profile-modal" id="pfNavbarProfileModal" tabindex="-1" aria-labelledby="pfNavbarProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="pfNavbarProfileModalLabel">
                    <i class="bi bi-person-circle me-2"></i>โปรไฟล์ของฉัน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="profile-modal-hero">
                    <img src="{{ $navbarProfile['avatar'] }}" alt="{{ $navbarProfile['display_name'] }}" class="profile-modal-avatar">
                    <div class="min-w-0">
                        <div class="profile-modal-name">{{ $navbarProfile['display_name'] }}</div>
                        <div class="profile-modal-username mt-1">{{ $navbarProfile['username'] }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="profile-modal-pill">{{ $navbarProfile['role_label'] }}</span>
                            <span class="profile-modal-pill">{{ $navbarProfile['branch_name'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="profile-modal-grid mt-4">
                    <div>
                        <div class="profile-modal-label">ชื่อเต็ม</div>
                        <div class="profile-modal-value">{{ $navbarProfile['full_name'] ?? $navbarProfile['display_name'] }}</div>
                    </div>
                    <div>
                        <div class="profile-modal-label">Username</div>
                        <div class="profile-modal-value">{{ $navbarProfile['username'] }}</div>
                    </div>
                    <div>
                        <div class="profile-modal-label">ตำแหน่ง</div>
                        <div class="profile-modal-value">{{ $navbarProfile['position'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="profile-modal-label">ชื่อเล่น</div>
                        <div class="profile-modal-value">{{ $navbarProfile['nickname'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="profile-modal-label">เบอร์โทร</div>
                        <div class="profile-modal-value">{{ $navbarProfile['phone'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="profile-modal-label">สาขา</div>
                        <div class="profile-modal-value">{{ $navbarProfile['branch_name'] ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ $navbarProfile['profile_url'] }}" class="btn btn-outline-primary rounded-pill px-4">
                    ดูโปรไฟล์เต็ม
                </a>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
    function pfOpenNavbarProfileModal() {
        var modalEl = document.getElementById('pfNavbarProfileModal');
        if (!modalEl) return;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');

        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show pf-fallback';
        document.body.appendChild(backdrop);
    }
</script>
