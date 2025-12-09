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
     * License check disabled for open-source version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // License check disabled - allow all requests
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
