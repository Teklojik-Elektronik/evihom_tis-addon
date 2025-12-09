<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Redirect;
use Exception;

class LicenseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks license validity by verifying with a remote server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // First check if we have a valid license in cache
        $license = Cache::get('app_license');

        // If no valid license is cached, fetch it from remote
        if (!$license) {
            $license = $this->fetchLicenseFromRemote();

            // Only cache the license if it's valid
            if ($license && isset($license['status']) && $license['status'] === 'success') {
                Cache::put('app_license', $license, 30);
            } else if ($license === null) {
                // If fetching the license failed, log the error
                Log::error('Failed to fetch license from remote server.');
                Alert::add("error", 'License verification failed.
                            please make sure your device has integrations installed and work properly')->flash();
                return Redirect::to(backpack_url('dashboard'));
            } else if ($license['status'] === 401) {
                // If the license is expired, log the error
                Log::error('License expired: ' . ($license['message'] ?? 'Unknown error.'));
                Alert::add("error", 'License expired. Please contact support.')->flash();
                return Redirect::to(backpack_url('dashboard'));
            } else {
                // If the license is invalid, log the error
                Log::error('License verification failed: ' . ($license['message'] ?? 'Unknown error.'));
                Alert::add("error", 'License verification failed.
                            please enter the serial number of the device')->flash();
                return Redirect::to(route('register.index'));
            }
        }

        // Handle scenarios where license data is unavailable
        if (is_null($license)) {
            Log::error('License verification failed: unable to retrieve license data.');
            return Redirect::to(route('register.index'));
        }

        // Check the response for validity
        if (isset($license['status'])) {
            if ($license['status'] === 401) {
                Alert::add("error", 'License verification failed. Please contact support.')->flash();
                return Redirect::to(backpack_url('dashboard'));
            }

            if (!$license['status']) {
                Log::warning('License check failed: ' . ($license['message'] ?? 'Unknown error.'));
                return Response::make($license['message'] ?? 'License validation failed.', 403);
            }
        } else {
            Log::error('Invalid license data structure.');
            return Response::make('Invalid license data.', 403);
        }

        // Log successful license verification
        Log::info('License verified successfully.');

        return $next($request);
    }

    /**
     * Fetch license data from the remote license server.
     *
     * @return array|null
     */
    protected function fetchLicenseFromRemote()
    {
        try {
            // first get license key
            $resp = Http::get('http://homeassistant.local:8123/api/get_key')->json();

            if ($resp !== null && isset($resp['key'])) {
                $key = $resp['key'];

                $response = Http::withToken(config('license.api_key'))
                    ->get(config('license.server_url') . 'verify', [
                        'mac' => $key
                    ]);

                if ($response->status() === 200 && $response->json()['status'] === 'success') {
                    session(['mac_address' => $key]);
                    return $response->json();
                }

                // Handle HTTP error statuses
                if ($response->status() === 401) {
                    Log::error('Unauthorized access to license server.');
                    return [
                        'status' => 401,
                        'message' => 'License expired'
                    ];
                } elseif ($response->status() === 404) {
                    Log::error('License endpoint not found on server.');
                    return [
                        'status' => 404,
                        'message' => 'Unauthorized'
                    ];
                } else {
                    Log::error('License server responded with status: ' . $response->status());
                }

                return null; // Return null if the response was not successful
            } else { // No key found
                Log::error('Failed to retrieve license key from local server.');
                return null; // Return null if the key is not found
            }
        } catch (Exception $e) {
            Log::error('Error fetching license from remote server: ' . $e->getMessage());
            return null; // Return null on error
        }
    }
}
