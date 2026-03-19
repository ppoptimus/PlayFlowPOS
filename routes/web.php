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

        Route::get('/massage-rooms', 'MassageRoomController@index')->name('massage-rooms');
        Route::get('/massage-rooms/rooms/{roomId}/edit', 'MassageRoomController@edit')->name('massage-rooms.rooms.edit');
        Route::post('/massage-rooms/rooms', 'MassageRoomController@storeRoom')->name('massage-rooms.rooms.store');
        Route::put('/massage-rooms/rooms/{roomId}', 'MassageRoomController@updateRoom')->name('massage-rooms.rooms.update');
        Route::delete('/massage-rooms/rooms/{roomId}', 'MassageRoomController@destroyRoom')->name('massage-rooms.rooms.destroy');
        Route::post('/massage-rooms/beds', 'MassageRoomController@storeBed')->name('massage-rooms.beds.store');
        Route::put('/massage-rooms/beds/{bedId}', 'MassageRoomController@updateBed')->name('massage-rooms.beds.update');
        Route::delete('/massage-rooms/beds/{bedId}', 'MassageRoomController@destroyBed')->name('massage-rooms.beds.destroy');

        Route::get('/masseuse/create', 'MasseuseController@create')->name('masseuse.create');
        Route::get('/masseuse/{staffId}/edit', 'MasseuseController@edit')->name('masseuse.edit');
        Route::post('/masseuse', 'MasseuseController@store')->name('masseuse.store');
        Route::put('/masseuse/{staffId}', 'MasseuseController@update')->name('masseuse.update');
        Route::delete('/masseuse/{staffId}', 'MasseuseController@destroy')->name('masseuse.destroy');

        Route::get('/packages', 'PackageController@index')->name('packages');
        Route::post('/packages', 'PackageController@store')->name('packages.store');
        Route::put('/packages/{packageId}', 'PackageController@update')->name('packages.update');

        Route::get('/products', 'ProductController@index')->name('products');
        Route::post('/products', 'ProductController@store')->name('products.store');
        Route::put('/products/{productId}', 'ProductController@update')->name('products.update');
        Route::delete('/products/{productId}', 'ProductController@destroy')->name('products.destroy');
        Route::post('/products/categories', 'ProductController@storeCategory')->name('products.categories.store');
        Route::put('/products/categories/{categoryId}', 'ProductController@updateCategory')->name('products.categories.update');
        Route::delete('/products/categories/{categoryId}', 'ProductController@deleteCategory')->name('products.categories.destroy');
        Route::post('/products/{productId}/adjust-stock', 'ProductController@adjustStock')->name('products.adjust-stock');

        // โซนจัดการการตั้งค่าระบบ (Admin Only)
        Route::get('/admin/commission', 'CommissionConfigController@index')->name('admin.commission.index');
        Route::post('/admin/commission', 'CommissionConfigController@store')->name('admin.commission.store');
        Route::delete('/admin/commission/{id}', 'CommissionConfigController@destroy')->name('admin.commission.destroy');
    });

    $modules = [
        'staff', 'promotions',
        'reports', 'financial', 'branches', 'users',
    ];

    foreach ($modules as $module) {
        Route::get('/' . $module, 'ModuleController@comingSoon')->name($module);
    }
});
