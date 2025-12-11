<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Settings;
use App\Models\DeviceType;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::all();
        return response()->json($devices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $device = Device::create($request->all());
        return response()->json("store");
    }

    public function scan(Request $request)
    {
        // Initialize the Guzzle client with timeout settings
        $client = new \GuzzleHttp\Client([
            'timeout' => 35, // Slightly higher than expected scan time
        ]);

        // Specify the API endpoint
        $server_address = Settings::where("key", "server_address")->first()->value;
        $url = $server_address . '/api/scan_devices';

        try {
            // Make a GET request to the API endpoint
            $response = $client->request('GET', $url);

            // Get the response body
            $devices = json_decode($response->getBody()->getContents(), true);
            $devices_length = count($devices);

            // Store each device in the database
            $processed = 0;
            $failed = 0;

            foreach ($devices as $device) {
                try {
                    $deviceTypeCodeStr = implode(',', $device['device_type_code']);
                    try {
                        $deviceTypeName = DeviceType::where('device_model_number', $deviceTypeCodeStr)->firstOrFail()->device_type_name;
                    } catch (ModelNotFoundException $e) {
                        Log::info("Device type name for model number " . $deviceTypeCodeStr . " not found");
                        $failed++;
                        continue;
                    }

                    if (in_array($deviceTypeCodeStr, DeviceType::all()->pluck('device_model_number')->toArray())) {
                        $device = Device::updateOrCreate(
                            [
                                'device_address' => implode(',', $device['device_id'])
                            ],
                            [
                                'device_type' => DeviceType::where('device_model_number', $deviceTypeCodeStr)->first()->id,
                                'gateway' => implode('.', $device['gateway']),
                                'device_name' => $deviceTypeName . " " . implode(', ', $device['device_id']),
                            ]
                        );
                        
                        // Auto-create appliances for this device if it has none
                        if ($device->appliances()->count() == 0) {
                            try {
                                $device->create_appliances();
                                Log::info("Auto-created appliances for device: " . $device->device_name);
                            } catch (\Exception $e) {
                                Log::warning("Could not auto-create appliances for device " . $device->device_name . ": " . $e->getMessage());
                            }
                        }
                        
                        $processed++;
                    } else {
                        Log::info("Device type " . $deviceTypeCodeStr . " not found");
                        $failed++;
                    }
                } catch (\Exception $e) {
                    Log::error($e);
                    $failed++;

                    if ($request->ajax()) {
                        return response()->json(['error' => 'Unable to update or create device'], 500);
                    }
                    Alert::error('Unable to update or create device')->flash();
                    return redirect()->back();
                }
            }

            // Prepare success message with details
            $message = "$processed devices added successfully";
            if ($failed > 0) {
                $message .= " ($failed devices could not be processed)";
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            Alert::success($message)->flash();
            return redirect()->route("device.index");
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->ajax()) {
                return response()->json(['error' => 'Unable to retrieve devices: ' . $e->getMessage()], 500);
            }
            Alert::error('Unable to retrieve devices')->flash();
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        return view('devices.show', compact('device'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $device->update($request->all());
        return redirect()->route('devices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        try {
            // Unpublish all appliances from Home Assistant first
            foreach ($device->appliances as $appliance) {
                if ($appliance->is_published) {
                    $applianceController = new \App\Http\Controllers\ApplianceController();
                    $request = new \Illuminate\Http\Request();
                    $applianceController->unpublish($request, $appliance->id);
                }
            }
            
            // Delete device (cascade will delete appliances)
            $device->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Device and all appliances deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available channels for a device
     */
    public function getChannels(Device $device)
    {
        try {
            $channels = $device->getAvailableChannels();
            return response()->json([
                'success' => true,
                'device' => [
                    'id' => $device->id,
                    'name' => $device->device_name,
                    'address' => $device->device_address,
                    'is_grouped' => $device->is_grouped,
                ],
                'channels' => $channels
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get channels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load channels'
            ], 500);
        }
    }

    /**
     * Create appliances from selected channels
     */
    public function createAppliancesFromSelection(Request $request, Device $device)
    {
        try {
            $selectedChannels = $request->input('channels', []);
            
            if (empty($selectedChannels)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No channels selected'
                ], 400);
            }

            $device->createSelectedAppliances($selectedChannels);

            return response()->json([
                'success' => true,
                'message' => 'Appliances created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create appliances: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create appliances: ' . $e->getMessage()
            ], 500);
        }
    }
}
