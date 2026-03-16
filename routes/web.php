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
    Route::get('/booking', 'BookingController@index')->name('booking');
    Route::get('/booking/data', 'BookingController@data')->name('booking.data');
    Route::post('/booking', 'BookingController@store')->name('booking.store');
    Route::put('/booking/{bookingId}', 'BookingController@update')->name('booking.update');
    Route::delete('/booking/{bookingId}', 'BookingController@destroy')->name('booking.destroy');
    Route::get('/staff', 'StaffController@index')->name('staff');

    $modules = [
        'receipts', 'customers', 'membership', 'packages',
        'masseuse', 'commissions', 'products', 'promotions',
        'reports', 'financial', 'branches', 'users',
    ];

    foreach ($modules as $module) {
        Route::get('/' . $module, 'ModuleController@comingSoon')->name($module);
    }
});
