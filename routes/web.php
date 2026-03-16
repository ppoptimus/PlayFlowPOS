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
    Route::get('/', 'PlayFlowController@dashboard')->name('dashboard');

    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/pos', 'PlayFlowController@pos')->name('pos');
    Route::get('/booking', 'PlayFlowController@booking')->name('booking');
    Route::get('/staff', 'PlayFlowController@staff')->name('staff');

    $modules = [
        'receipts', 'customers', 'membership', 'packages',
        'masseuse', 'commissions', 'products', 'promotions',
        'reports', 'financial', 'branches', 'users',
    ];

    foreach ($modules as $module) {
        Route::get('/' . $module, 'PlayFlowController@comingSoon')->name($module);
    }
});
