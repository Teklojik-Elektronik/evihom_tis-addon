<?php

namespace Database\Seeders;

use App\Models\ApplianceType;
use Illuminate\Database\Seeder;

class ApplianceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $TIS_APPLIANCES = [
            "switch",
            "dimmer",
            "rgbw",
            "rgb",
            "ac",
            "floor_heating",
            "shutter",
            "motor",
            "binary_sensor",
            "security",
            "analog_sensor",
            "energy_sensor",
            "universal_switch",
            "health_sensor",
            "lux_sensor", // 10 fun
            "temperature_sensor", // 10 fun
            // "weather",
        ];

        // first empty the table
        ApplianceType::truncate();

        // now populate it
        foreach ($TIS_APPLIANCES as $appliance) {
            ApplianceType::create([
                'appliance_type_name' => $appliance,
                'is_protected' => false,
            ]);
        }
    }
}
