<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appliance;
use App\Models\Device;
use App\Models\ApplianceChannels;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Prologue\Alerts\Facades\Alert as Alert;
use Illuminate\Support\Facades\DB;

class ApplianceController extends Controller
{
    public function publish(Request $request)
    {
        $channels =  ApplianceChannels::all();
        foreach ($channels as $channel) {
            if ($channel->appliance_id <= 0 || $channel->channel_number == null) {
                // Set a success message in the session
                Alert::add('error', 'Error publishing appliances, missing device or channel number on ' . $channel->applianceId->appliance_name . $channel->channel_name)->flash();
                Log::info('Error publishing appliances, missing device or channel number on ' . $channel->applianceId->appliance_name . $channel->channel_name);
                return Redirect::to(backpack_url('appliance-channels'));
            }
        }
        $server_address = Settings::where("key", "server_address")->first()->value;
        $appliance_channels = ApplianceChannels::select('appliance_id', 'channel_number', 'channel_name')->get();
        $appliance_channels->transform(function ($channel) {
            $channel->appliance_name = $channel->appliance->appliance_name;
            $channel->is_protected = $channel->applianceId->is_protected;
            $channel->device_id = $channel->device->device_address;
            $channel->gateway = $channel->device->gateway;
            $channel->appliance_type = $channel->applianceId->applianceType->appliance_type_name;
            $channel->min = $channel->appliance->min;
            $channel->max = $channel->appliance->max;
            $channel->settings = $channel->appliance->settings;
            unset($channel->applianceId);
            unset($channel->appliance);
            unset($channel->device);
            return $channel;
        });

        $grouped_channels = $appliance_channels->groupBy('appliance_name');
        $devices = Device::select('device_type', 'device_name', 'device_address')->get();
        $devices->transform(function ($device) {
            $device->device_type = $device->deviceType->device_type_name;
            unset($device->deviceType);
            return $device;
        });

        // get the configs
        $configs = [
            'lock_module_password' => Settings::where('key', 'lock_module_password')->first()->value,
        ];

        $payload = [
            'devices' => $devices,
            'appliances' => $grouped_channels,
            'configs' => $configs,
        ];

        // save a copy of the project locally and on cms first, then publish it to home assistant
        $response = Http::get('http://homeassistant.local:8123/api/get_key')->json();
        if ($response !== null && isset($response['key'])) {
            $mac_address = $response['key'];
        }
        try {
            if (isset($mac_address)) {
                $tables = ['devices', 'appliances', "appliance_channels", "devices_types", "appliance_types"];
                $data = [];

                foreach ($tables as $table) {
                    $data[$table] = DB::table($table)->get();
                }

                $json = json_encode($data);
                $response = Http::withToken(config('cms.api_key'))->post(config('cms.save_project'), [
                    'saved_project' => $json,
                    'mac_address' => $mac_address
                ]);
                if ($response->status() !== 200) {
                    Log::error('Failed to save project on CMS: ' . $response->body());
                    Alert::add('error', 'Project couldn\'t be saved on CMS')->flash();
                } else {
                    Log::info('Project saved on CMS: ' . $response->body());
                    Alert::add('success', 'Project saved on CMS successfully')->flash();
                }
            } else {
                Log::error('Failed to retrieve license key from local server.');
                Alert::add('error', 'Project couldn\'t be saved on CMS')->flash();
            }
        } catch (\Exception $e) {
            Log::error('Error saving project on CMS: ' . $e->getMessage());
            Alert::add('error', 'Project couldn\'t be saved on CMS')->flash();
        }

        try {
            $response = Http::post($server_address . '/api/tis', $payload);
            Alert::Add('success', 'Successfully published the appliances')->flash();
            return back()->with('success', 'Request successful.');
        } catch (\Exception $e) {
            Alert::add('error', 'Error publishing appliances,' . str($e))->flash();
            return back();
        }
    }

    /**
     * Check for existing appliances
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExisting(Request $request)
    {
        $unassociated_devices = Device::getUnassociatedDevices();

        // If device_ids are provided, check appliances for those specific devices
        if ($request->has('device_ids') && is_array($request->device_ids)) {
            $count = Appliance::whereIn('device_id', $request->device_ids)->count();
        } else {
            // Otherwise, count all appliances
            $count = Appliance::count();
        }

        return response()->json([
            'count' => $count,
            'orphan_devices' => $unassociated_devices
        ]);
    }

    /**
     * Auto create appliances for devices
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auto_create(Request $request)
    {
        DB::beginTransaction();
        try {
            // If specific device IDs were provided
            if ($request->has('device_ids') && is_array($request->device_ids)) {
                $device_ids = $request->device_ids;

                // If confirmation is required, delete existing appliances for these devices
                if ($request->input('confirm') == true) {
                    // Get the appliance IDs associated with these devices
                    $appliance_ids = Appliance::whereIn('device_id', $device_ids)->pluck('id')->toArray();

                    // Delete the associated appliance channels
                    ApplianceChannels::whereIn('appliance_id', $appliance_ids)->delete();

                    // Delete the appliances
                    Appliance::whereIn('device_id', $device_ids)->delete();
                }

                // Get the selected devices
                $devices = Device::whereIn('id', $device_ids)->get();
            } else {
                // If no device IDs were provided, process all devices
                if ($request->input('confirm') == true) {
                    // Delete all appliances and channels
                    ApplianceChannels::query()->delete();
                    Appliance::query()->delete();
                }

                // Get all devices
                $devices = Device::all();
            }

            // Create appliances for each device
            foreach ($devices as $device) {
                $device->create_appliances();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Error Creating Appliances: ' . $th->getMessage());
            return response()->json(['success' => false, 'error' => 'Error creating appliances: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Appliance $appliance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appliance $appliance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appliance $appliance)
    {
        //
    }
}
