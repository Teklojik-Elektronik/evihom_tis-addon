<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ApplianceController;
use App\Http\Controllers\HASSLockController;
use App\Http\Controllers\HASSEnergyController;
use App\Models\VirtualDevice;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/outputchannel/set', [ChannelController::class, 'setOutState'])->name('outputchannel.setstate');
Route::get('/outputchannel/{id}', [ChannelController::class, 'getOutState'])->name('outputchannel.getstate');


// create routes for the DeviceController
Route::get('devices', [DeviceController::class, 'index'])->name('devices.index');
Route::get('devices/create', [DeviceController::class, 'create'])->name('devices.create');
Route::post('devices', [DeviceController::class, 'store'])->name('devices.store');
Route::get('devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
Route::get('devices/{device}/edit', [DeviceController::class, 'edit'])->name('devices.edit');
Route::put('devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
Route::delete('devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

// routes for the ApplianceController
Route::get('appliances/publish', [ApplianceController::class, 'publish'])->name('appliances.publish');
Route::get('appliances/publish-all', [ApplianceController::class, 'publishAll'])->name('appliances.publish_all');
Route::get('appliances/publish-single/{id}', [ApplianceController::class, 'publishSingle'])->name('appliances.publish_single');
Route::get('appliances/unpublish/{id}', [ApplianceController::class, 'unpublish'])->name('appliances.unpublish');
Route::get('appliances/check-existing', [ApplianceController::class, 'checkExisting'])->name('appliances.check-existing');
Route::post('appliances/auto-create', [ApplianceController::class, 'auto_create'])->name('appliances.auto-create');

Route::get('search', function (Request $request) {
    $search = $request->get('term');

    if (strlen($search) >= 1) {
        $results = VirtualDevice::where('device_name', 'LIKE', '%' . $search . '%')->get();

        return $results->map(function ($result) {
            return $result->device_name;
        });
    }
    return [];
});
Route::get('save_project', [ProjectController::class, 'saveProject'])->name('save_project');
Route::post('load_project', [ProjectController::class, 'loadProject'])->name('load_project');

Route::get('change-password', [HASSLockController::class, 'index'])->name('change-password');
Route::post('change-pass', [HASSLockController::class, 'change_pass'])->name('change-pass');

Route::get('electricity-bill', [HASSEnergyController::class, 'index'])->name('electricity-bill');
Route::post('configure-bill', [HASSEnergyController::class, 'configure_bill'])->name('configure-bill');
Route::post('get-bill-configs', [HASSEnergyController::class, 'get_bill_configs'])->name('get-bill-configs');

Route::post('/update-server', function (Request $request) {
    try {
        $resp = Http::get('http://homeassistant.local:8123/api/get_key')->json();

        if ($resp !== null && isset($resp['key'])) {
            $mac_address = $resp['key'];
            $response = Http::get('http://homeassistant.local:8123/api/update', ['mac_address' => $mac_address])->json();

            if ($response !== null && isset($response['status']) && $response['status'] === 'success') {
                Log::info("Server updated successfully with MAC address: $mac_address");
                return response()->json(['message' => 'Server updated successfully.']);
            } else {
                Log::error("Failed to update server: " . ($response['message'] ?? 'Unknown error.'));
                return response()->json(['error' => 'Failed to update server: ' . ($response['message'] ?? 'Unknown error.')], 500);
            }
        } else {
            Log::error("Failed to retrieve MAC address from Home Assistant.");
            return response()->json(['error' => 'Failed to retrieve MAC address from Home Assistant.'], 500);
        }
    } catch (\Exception $e) {
        Log::error("Exception occurred while updating server: " . $e->getMessage());
        return response()->json(['error' => 'Failed to update server: ' . $e->getMessage()], 500);
    }
})->name('update-server');
