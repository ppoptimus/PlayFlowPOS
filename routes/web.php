<?php
use Illuminate\Support\Facades\Route;

// 4 โมดูลหลัก
Route::get('/', 'PlayFlowController@dashboard')->name('dashboard');
Route::get('/pos', 'PlayFlowController@pos')->name('pos');
Route::get('/booking', 'PlayFlowController@booking')->name('booking');
Route::get('/staff', 'PlayFlowController@staff')->name('staff');

// โมดูลที่เหลือทั้งหมด ชี้ไปที่ comingSoon (ใช้ Loop เดียวพอครับ ไม่ต้องประกาศแยกทีละบรรทัด)
$modules = [
    'queue', 'receipts', 'customers', 'membership', 'packages', 
    'masseuse', 'commissions', 'products', 'promotions', 
    'reports', 'financial', 'branches', 'users'
];

foreach ($modules as $m) {
    Route::get('/' . $m, 'PlayFlowController@comingSoon')->name($m);
}
