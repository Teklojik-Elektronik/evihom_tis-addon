<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use Prologue\Alerts\Facades\Alert;

class RegisterController extends Controller
{
    public function index()
    {
        $license = Cache::get('app_license');
        if ($license && isset($license['status']) && $license['status'] === 'success') {
            Alert::add("success", 'License is valid.')->flash();
            return redirect()->back();
        }

        try {
            $response = Http::get('https://restcountries.com/v3.1/all?fields=name');
            $countries = collect(json_decode($response->body(), true))
                ->pluck('name.common')
                ->sort()
                ->toArray();
            return view('register_device', ['countries' => $countries]);
        } catch (Exception $e) {
            // Fallback to a basic list if API fails
            Log::error('Failed to fetch countries: ' . $e->getMessage());
            Alert::error('Please check your network connection')->flash();
            redirect()->to(route('dashboard'));
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'serial_number' => 'required|string|max:255',
                'country' => 'required|string|max:255',
            ]);
            $serial_number = $request->input('serial_number');
            $country = $request->input('country');

            $resp = Http::get('http://homeassistant.local:8123/api/get_key')->json();
            if ($resp) {
                $mac_address = $resp['key'] ?? null;
                if ($mac_address === null) {
                    Alert::add("error", 'Error registering your device,
                                please make sure your device has integrations installed and work properly')->flash();
                    return Redirect::to(backpack_url('dashboard'));
                }

                $response = Http::withToken(config('license.api_key'))
                    ->get(config('license.server_url') . 'register', [
                        'serial_number' => $serial_number,
                        'country' => $country,
                        'mac' => $mac_address,
                    ]);

                if ($response->status() === 200 && $response->json()['status'] === 'success') {
                    Log::info('Device registered successfully: ' . $response->json()['message']);
                    Alert::add("success", 'Device registered successfully')->flash();
                    return Redirect::to(backpack_url('dashboard'));
                } else if ($response->status() === 401) {
                    Log::error('License Expired: ' . $response->json()['message']);
                    Alert::add("error", 'License Expired, please contact support.')->flash();
                    return Redirect::to(backpack_url('dashboard'));
                } else {
                    Log::error('Error registering device: ' . $response->json()['message']);
                    Alert::add("error", 'Error registering your device, please make sure you have entered the serial number correctly')->flash();
                    return Redirect::to(route('register.index'));
                }
            } else {
                Log::error('Error fetching Key from device.');
                Alert::add("error", 'Error registering your device,
                            please make sure your device has integrations installed and work properly')->flash();
                return Redirect::to(backpack_url('dashboard'));
            }
        } catch (Exception $e) {
            Log::error('Error registering device: ' . $e->getMessage());
            Alert::add("error", 'Error registering your device,
                        please make sure your device has integrations installed and work properly')->flash();
            return Redirect::to(backpack_url('dashboard'));
        }
    }
}
