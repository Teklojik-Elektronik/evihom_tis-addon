<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect(backpack_url('dashboard'));
});

Route::get('devices/scan', [DeviceController::class, 'scan'])->name('devices.scan');
Route::get('devices/{device}/channels', [DeviceController::class, 'getChannels'])->name('devices.channels');
Route::post('devices/{device}/create-appliances', [DeviceController::class, 'createAppliancesFromSelection'])->name('devices.create_appliances');

// Language routes
Route::post('language/change', [LanguageController::class, 'changeLanguage'])->name('language.change');
Route::get('language/current', [LanguageController::class, 'getCurrentLanguage'])->name('language.current');
