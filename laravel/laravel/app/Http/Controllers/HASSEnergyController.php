<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class HASSEnergyController extends Controller
{
    public function index()
    {
        return view('hassenergy');
    }

    public function get_bill_configs(Request $request)
    {
        $response = Http::post("homeassistant.local:8123/api/get-bill-config");
        Log::info("info: {$response}");
        if ($response->status() !== 200) {
            Log::error('Failed to fetch bill configurations from Home Assistant: ' . $response->body());
            return response()->json([
                'summer_rates' => [],
                'winter_rates' => [],
            ]);
        }

        if (!$response->json() || !isset($response->json()['config'])) {
            Log::error('Invalid response structure from Home Assistant: ' . $response->body());
            return response()->json([
                'summer_rates' => [],
                'winter_rates' => [],
            ]);
        }

        $configs = $response->json()['config'];
        return response()->json([
            'summer_rates' => $configs['summer_rates'] ?? [],
            'winter_rates' => $configs['winter_rates'] ?? [],
        ]);
    }

    public function configure_bill(Request $request)
    {
        $winter_rates = $request->input('winter_rates');
        $summer_rates = $request->input('summer_rates');

        for ($i = 1; $i < count($summer_rates); $i++) {
            if ($summer_rates[$i - 1]['min_kw'] >= $summer_rates[$i]['min_kw']) {
                Log::error("Validation error: Each summer rate value must be less than the next one.", [
                    'previous' => $summer_rates[$i - 1],
                    'current' => $summer_rates[$i],
                ]);
                return response()->json(['error' => 'Each summer rate value must be less than the next one.'], 422);
            }
        }

        for ($i = 1; $i < count($winter_rates); $i++) {
            if ($winter_rates[$i - 1]['min_kw'] >= $winter_rates[$i]['min_kw']) {
                Log::error("Validation error: Each summer rate value must be less than the next one.", [
                    'previous' => $winter_rates[$i - 1],
                    'current' => $winter_rates[$i],
                ]);
                return response()->json(['error' => 'Each summer rate value must be less than the next one.'], 422);
            }
        }

        $data = ["summer_rates" => $summer_rates, "winter_rates" => $winter_rates];

        $response = Http::post("homeassistant.local:8123/api/bill-config", $data);
        if ($response->status() !== 200) {
            Log::error('Failed to configure bill on Home Assistant: ' . $response->body());
            return response()->json(['error' => 'Failed to configure bill on Home Assistant.'], 500);
        }

        return response()->json(['success' => 'Bill Configuration Saved Successfully.']);
    }
}
