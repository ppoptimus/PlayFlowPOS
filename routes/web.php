<?php
use Illuminate\Support\Facades\Route;

// Core Mockup Pages
Route::get('/', 'PlayFlowController@dashboard')->name('dashboard');
Route::get('/pos', 'PlayFlowController@pos')->name('pos');
Route::get('/booking', 'PlayFlowController@booking')->name('booking');
Route::get('/staff', 'PlayFlowController@staff')->name('staff');
// เพิ่มต่อจากเส้นทางเดิมที่มีอยู่
Route::get('/queue', 'PlayFlowController@comingSoon')->name('queue');
Route::get('/receipts', 'PlayFlowController@comingSoon')->name('receipts');
Route::get('/customers', 'PlayFlowController@comingSoon')->name('customers');
Route::get('/membership', 'PlayFlowController@comingSoon')->name('membership');
Route::get('/packages', 'PlayFlowController@comingSoon')->name('packages');
Route::get('/masseuse', 'PlayFlowController@comingSoon')->name('masseuse');
Route::get('/commissions', 'PlayFlowController@comingSoon')->name('commissions');
Route::get('/products', 'PlayFlowController@comingSoon')->name('products');
Route::get('/promotions', 'PlayFlowController@comingSoon')->name('promotions');
Route::get('/reports', 'PlayFlowController@comingSoon')->name('reports');
Route::get('/financial', 'PlayFlowController@comingSoon')->name('financial');
Route::get('/branches', 'PlayFlowController@comingSoon')->name('branches');
Route::get('/users', 'PlayFlowController@comingSoon')->name('users');

// Remaining Modules (Redirect to Coming Soon)
$modules = [
    'queue', 'receipts', 'customers', 'membership', 'packages', 
    'masseuse', 'commissions', 'products', 'promotions', 
    'reports', 'financial', 'branches', 'users'
];

foreach ($modules as $m) {
    Route::get('/' . $m, 'PlayFlowController@comingSoon')->name($m);
}