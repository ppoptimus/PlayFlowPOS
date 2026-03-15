<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', 'PlayFlowController@dashboard')->name('dashboard');
Route::get('/pos', 'PlayFlowController@pos')->name('pos');
Route::get('/booking', 'PlayFlowController@booking')->name('booking');
Route::get('/staff', 'PlayFlowController@staff')->name('staff');
