<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Appliance;
use Illuminate\Http\Request;

class DeviceApplianceTreeController extends Controller
{
    public function index()
    {
        // Only show devices that have at least one appliance
        $devices = Device::with('deviceType')
            ->has('appliances')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'device_name' => $device->device_name,
                    'device_address' => $device->device_address,
                    'device_type' => $device->deviceType->device_type_name ?? 'Unknown',
                ];
            });

        $appliances = Appliance::with(['applianceType', 'deviceId'])->get()->map(function ($appliance) {
            return [
                'id' => $appliance->id,
                'appliance_name' => $appliance->appliance_name,
                'device_id' => $appliance->device_id,
                'appliance_type_name' => $appliance->applianceType->appliance_type_name ?? 'Unknown',
                'is_published' => $appliance->is_published ?? false,
            ];
        });

        return view('admin.device_appliance_tree', [
            'devices' => $devices,
            'appliances' => $appliances,
        ]);
    }
}
