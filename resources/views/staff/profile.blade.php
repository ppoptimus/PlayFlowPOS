@extends('layouts.main')

@section('title', 'โปรไฟล์ของฉัน - PlayFlow')
@section('page_title', 'โปรไฟล์ของฉัน')
@section('page_subtitle', 'My Profile')

@push('head')
<style>
    .profile-page .profile-card,
    .profile-page .info-card {
        border-radius: 1.2rem;
        border: 1px solid rgba(31, 115, 224, 0.14);
        box-shadow: 0 18px 34px rgba(17, 81, 146, 0.08);
    }

    .profile-page .profile-card {
        background: linear-gradient(145deg, #f7fbff 0%, #ffffff 100%);
    }

    .profile-page .profile-hero {
        display: flex;
        flex-wrap: wrap;
        gap: 1.2rem;
        align-items: center;
    }

    .profile-page .profile-avatar {
        width: 116px;
        height: 116px;
        border-radius: 1.6rem;
        object-fit: cover;
        border: 3px solid rgba(31, 115, 224, 0.14);
        box-shadow: 0 16px 30px rgba(24, 82, 144, 0.14);
        background: #ffffff;
    }

    .profile-page .profile-name {
        font-size: 1.45rem;
        font-weight: 700;
        color: #1f456c;
        line-height: 1.15;
    }

    .profile-page .profile-subtitle {
        color: #6b7f93;
    }

    .profile-page .profile-pill {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.72rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f65b8;
        background: rgba(45, 143, 240, 0.12);
    }

    .profile-page .info-card {
        background: #ffffff;
    }

    .profile-page .info-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #5d7a98;
        margin-bottom: 0.25rem;
    }

    .profile-page .info-value {
        min-height: 48px;
        border-radius: 0.95rem;
        border: 1px solid rgba(31, 115, 224, 0.12);
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 0.78rem 0.92rem;
        color: #1f456c;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="row g-3 profile-page">
    <div class="col-12">
        <div class="card profile-card border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="profile-hero">
                    <img src="{{ $profile['avatar'] }}" alt="{{ $profile['display_name'] }}" class="profile-avatar">
                    <div class="flex-grow-1">
                        <div class="profile-name">{{ $profile['display_name'] }}</div>
                        <div class="profile-subtitle mt-1">{{ $profile['username'] }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="profile-pill">{{ $profile['role_label'] }}</span>
                            <span class="profile-pill">{{ $profile['branch_name'] }}</span>
                            @if(($profile['kind'] ?? '') === 'staff')
                            <span class="profile-pill">พนักงาน</span>
                            @elseif(($profile['kind'] ?? '') === 'masseuse')
                            <span class="profile-pill">หมอนวด</span>
                            @else
                            <span class="profile-pill">บัญชีระบบ</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-house-door me-1"></i>กลับหน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card info-card border-0">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">ชื่อเต็ม</div>
                        <div class="info-value">{{ $profile['full_name'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">Username</div>
                        <div class="info-value">{{ $profile['username'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">บทบาท</div>
                        <div class="info-value">{{ $profile['role_label'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">สาขา</div>
                        <div class="info-value">{{ $profile['branch_name'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">ตำแหน่ง</div>
                        <div class="info-value">{{ $profile['position'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">ชื่อเล่น</div>
                        <div class="info-value">{{ $profile['nickname'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">เบอร์โทร</div>
                        <div class="info-value">{{ $profile['phone'] }}</div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">ประเภทข้อมูล</div>
                        <div class="info-value">
                            @if(($profile['kind'] ?? '') === 'staff')
                            พนักงานทั่วไป
                            @elseif(($profile['kind'] ?? '') === 'masseuse')
                            หมอนวด
                            @else
                            บัญชีระบบ
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="info-label">รูปโปรไฟล์</div>
                        <div class="info-value">{{ $profile['has_profile_image'] ? 'มีรูปโปรไฟล์แล้ว' : 'กำลังใช้รูปสำรองของระบบ' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
