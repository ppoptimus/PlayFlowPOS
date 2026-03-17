<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login')->name('login.attempt');
});

Route::post('/logout', 'Auth\LoginController@logout')
    ->name('logout')
    ->middleware('auth');

Route::middleware('auth')->group(function (): void {
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/pos', 'PosController@index')->name('pos');
    Route::post('/pos/checkout', 'PosController@checkout')->name('pos.checkout');
    Route::get('/receipts', 'ReceiptController@index')->name('receipts');
    Route::get('/receipts/{orderId}', 'ReceiptController@show')->name('receipts.show');
    Route::get('/booking', 'BookingController@index')->name('booking');
    Route::get('/booking/data', 'BookingController@data')->name('booking.data');
    Route::post('/booking', 'BookingController@store')->name('booking.store');
    Route::put('/booking/{bookingId}', 'BookingController@update')->name('booking.update');
    Route::delete('/booking/{bookingId}', 'BookingController@destroy')->name('booking.destroy');
    Route::get('/masseuse', 'MasseuseController@index')->name('masseuse');
    Route::post('/masseuse/attendance', 'MasseuseController@updateAttendance')->name('masseuse.attendance');
    Route::get('/customers', 'CustomerController@index')->name('customers');
    Route::post('/customers', 'CustomerController@store')->name('customers.store');
    Route::post('/customers/quick-create', 'CustomerController@quickCreate')->name('customers.quick-create');
    Route::get('/customers/{customerId}/history', 'CustomerController@history')->name('customers.history');
    Route::put('/customers/{customerId}', 'CustomerController@update')->name('customers.update');
    Route::delete('/customers/{customerId}', 'CustomerController@destroy')->name('customers.destroy');
    Route::middleware('admin.only')->group(function (): void {
        Route::get('/membership-levels', 'MembershipLevelController@index')->name('membership-levels');
        Route::post('/membership-levels', 'MembershipLevelController@store')->name('membership-levels.store');
        Route::put('/membership-levels/{tierId}', 'MembershipLevelController@update')->name('membership-levels.update');
    });

    $modules = [
        'packages', 'staff',
        'commissions', 'products', 'promotions',
        'reports', 'financial', 'branches', 'users',
    ];

    foreach ($modules as $module) {
        Route::get('/' . $module, 'ModuleController@comingSoon')->name($module);
    }
});
