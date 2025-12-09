<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () {
    Route::post('register/device', [RegisterController::class, 'register'])->name('register.device');
    Route::get('register', [RegisterController::class, 'index'])->name('register.index');
    Route::get('logs', [LogController::class, 'index'])->name('logs');
    Route::get('log', fn () => redirect()->to(route('logs')));
    Route::get('logs/download', [LogController::class, 'download'])->name('logs.download');
    Route::get('logs/ajax', [LogController::class, 'ajaxRefresh'])->name('logs.ajax');
    Route::get('logs/clear', [LogController::class, 'clear'])->name('logs.clear');
});

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        // license middleware
        ["license"],
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('appliance', 'ApplianceCrudController');
    Route::crud('device', 'DeviceCrudController');
    Route::crud('device-type', 'DeviceTypeCrudController');
    // Route::crud('channel', 'ChannelCrudController');
    Route::crud('settings', 'SettingsCrudController');
    // Route::crud('rooms', 'RoomsCrudController');
    // Route::crud('floors', 'FloorsCrudController');
    Route::crud('appliance-type', 'ApplianceTypeCrudController');
    // Route::crud('virtual-device', 'VirtualDeviceCrudController');
    Route::crud('appliance-channels', 'ApplianceChannelsCrudController');
    
    // Device-Appliance Tree View
    Route::get('device-appliance-tree', 'DeviceApplianceTreeController@index')->name('device-appliance-tree');
    // resource routes
    Route::resource('handover', 'HandOverController');
    Route::crud('default-appliance', 'DefaultApplianceCrudController');
    Route::crud('default-appliance-channel', 'DefaultApplianceChannelCrudController');

    Route::get('cms', function () {
        return view('cms');
    })->name('cms');
}); // this should be the absolute last line of this file
