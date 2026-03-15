@extends('layouts.main')

@section('title', 'Coming Soon')
@section('page_title', 'Feature Coming Soon')

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-center p-5">
    <div class="py-5">
        <i class="bi bi-tools display-1 text-primary mb-4"></i>
        <h2 class="fw-bold">โมดูล {{ $module }}</h2>
        <p class="text-muted fs-5">ฟีเจอร์นี้กำลังอยู่ระหว่างการพัฒนาตามแผนงานงวดที่ 2</p>
        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> กลับหน้าหลัก
            </a>
        </div>
    </div>
</div>
@endsection