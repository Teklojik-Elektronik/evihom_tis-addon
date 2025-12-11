<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Represents a device model.
 *
 * @property int $id The unique identifier of the device.
 * @property string $device_type fk to the devices_types table.
 * @property string $device_name The name of the device.
 * @property string $address The adress of the device.
 * @property string $gateway The gateway of the device.
 */
class Device extends Model
{
    use CrudTrait;

    // Define the table associated with the model
    protected $table = 'devices';

    // Define the primary key column name
    protected $primaryKey = 'id';

    protected $fillable = [
        'device_name',
        'device_type',
        'device_address',
        'gateway',
        'is_grouped',
        'available_channels',
    ];

    protected $casts = [
        'available_channels' => 'array',
        'is_grouped' => 'boolean',
    ];

    // model events
    protected static function boot()
    {
        parent::boot();

        static::created(function ($device) {
            // Get the device type
            $deviceType = $device->deviceType;
            // Create the output channels
            // TODO: create default appliances
            // foreach ($deviceType->defaultAppliances as $defaultAppliance) {
            //     $appliance = new Appliance();
            //     $appliance->appliance_type = $defaultAppliance->appliance_type;
            //     $appliance->appliance_name = $device->device_name . "-" . $defaultAppliance->appliance_identifier;
            //     $appliance->device_id = $device->id;
            //     $appliance->is_protected = $defaultAppliance->is_protected || false;
            //     $appliance->save();
            // }

            // check if the device has a virtual device and sync
            // TODO: make virtual device work
            // $device->sync_virtual_device();

        });

        static::deleting(function ($device) {
            // TODO: delete all related appliances
            $device->appliances()->each(function ($channel) {
                $channel->delete();
            });
            $virtualDevice = $device->virtualDevice;
            // TODO: make virtual device work
            // if ($virtualDevice) {
            //     foreach ($device->applianceChannels as $applianceChannel) {
            //         $applianceChannel->update([
            //             'device_id' => $virtualDevice->id,
            //             'channel_type' => $applianceChannel->channel_type == 'input' ? 'virtual_input' : 'virtual_output',
            //         ]);
            //     }
            //     $virtualDevice->update(['is_mapped' => false]);
            //     Log::info("Virtual Device unmapped");
            // } else {
            //     Log::info("Virtual Device not found");
            // }
        });

        static::updated(function ($device) {
            Log::info('Device updated... syncing virtual device');
            // TODO: make virtual device work
            // $device->sync_virtual_device();
        });
    }

    public static function getUnassociatedDevices()
    {
        return self::whereDoesntHave('appliances')->pluck('id')->toArray();
    }

    public function create_appliances()
    {
        // for each device, get the device type and create the default appliances
        $defaultAppliances = $this->deviceType->defaultAppliances;
        foreach ($defaultAppliances as $defaultAppliance) {
            // get appliance type name
            $applianceTypeName = ApplianceType::find($defaultAppliance->appliance_type)->appliance_type_name;

            // create a new appliance
            $appliance = Appliance::create(
                [
                    'device_id' => $this->id,
                    'appliance_name' => $this->device_name . '_' . $applianceTypeName . '_' . $defaultAppliance->appliance_identifier,
                    'appliance_type' => $defaultAppliance->appliance_type,
                    'is_protected' => $defaultAppliance->appliancetype->is_protected,
                ]
            );

            // now create appliance channels using DefaultApplianceChannels
            $defaultApplianceChannels = DefaultApplianceChannel::where('appliance_type_id', $defaultAppliance->appliance_type)->get();
            foreach ($defaultApplianceChannels as $defaultApplianceChannel) {
                ApplianceChannels::updateOrCreate(
                    [
                        'appliance_id' => $appliance->id,
                        'channel_name' => $defaultApplianceChannel->channel_name,
                    ],
                    [
                        'channel_number' => $defaultAppliance->appliance_identifier,
                    ]
                );
            }
        }
    }

    // Relationships
    public function appliances()
    {
        return $this->hasMany(Appliance::class, 'device_id', 'id');
    }

    public function defaultAppliances()
    {
        return $this->hasMany(DefaultAppliance::class, 'device_type', 'device_type');
    }

    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class, 'device_type');
    }

    // TODO: make virtual device work
    // public function virtualDevice()
    // {
    //     return $this->hasOne(VirtualDevice::class, "device_name", "device_name");
    // }

    // BackPack CRUD Buttons
    public function save_project($crud = false)
    {
        return '<a class="btn btn-primary" href="' . route('save_project') . '" data-toggle="tooltip" title="Save project"><i class="la la-download "></i>Save Project</a>';
    }
    public function load_project($crud = false)
    {
        return '
            <form id="loadProjectForm" action="' . route('load_project') . '" method="POST" enctype="multipart/form-data" style="display: none;">
                ' . csrf_field() . '
                <input type="file" name="file" id="loadProjectFile" onchange="document.getElementById(\'loadProjectForm\').submit();" required>
            </form>
            <a class="btn btn-primary" href="#" onclick="document.getElementById(\'loadProjectFile\').click();" data-toggle="tooltip" title="Load Project">
                <i class="la la-upload"></i> Load Project
            </a>
        ';
    }

    public function scan_devices($crud = false)
    {
        return view('vendor.backpack.crud.buttons.scan-devices-button')->render();
    }

    /**
     * Get available channels for this device based on device type
     */
    public function getAvailableChannels()
    {
        if ($this->available_channels) {
            return $this->available_channels;
        }

        $channels = [];
        $defaultAppliances = $this->deviceType->defaultAppliances;

        foreach ($defaultAppliances as $defaultAppliance) {
            $applianceType = ApplianceType::find($defaultAppliance->appliance_type);
            
            $channelGroup = [
                'type' => $applianceType->appliance_type_name,
                'identifier' => $defaultAppliance->appliance_identifier,
                'is_protected' => $applianceType->is_protected,
                'channels' => []
            ];

            // Get default channels for this appliance type
            $defaultChannels = DefaultApplianceChannel::where('appliance_type_id', $defaultAppliance->appliance_type)->get();
            
            foreach ($defaultChannels as $channel) {
                $channelGroup['channels'][] = [
                    'name' => $channel->channel_name,
                    'number' => $defaultAppliance->appliance_identifier,
                ];
            }

            $channels[] = $channelGroup;
        }

        return $channels;
    }

    /**
     * Create selected appliances from channel selection
     */
    public function createSelectedAppliances(array $selectedChannels)
    {
        foreach ($selectedChannels as $selection) {
            $applianceType = ApplianceType::where('appliance_type_name', $selection['type'])->first();
            
            $appliance = Appliance::create([
                'device_id' => $this->id,
                'appliance_name' => $this->device_name . '_' . $selection['type'] . '_' . $selection['identifier'],
                'appliance_type' => $applianceType->id,
                'is_protected' => $selection['is_protected'] ?? false,
                'channel_identifier' => $selection['identifier'],
            ]);

            // Create appliance channels
            foreach ($selection['channels'] as $channel) {
                ApplianceChannels::updateOrCreate(
                    [
                        'appliance_id' => $appliance->id,
                        'channel_name' => $channel['name'],
                    ],
                    [
                        'channel_number' => $channel['number'],
                    ]
                );
            }
        }

        $this->update(['is_grouped' => true]);
    }

    public function auto_create_appliances($crud = false)
    {
        return '<a class="btn btn-primary" href="' . route('appliances.auto-create') . '" data-toggle="tooltip" title="Auto create appliances from the devices."><i class="la la-plus"></i> Auto Create Appliances</a>';
    }
}
