<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HASSLockController extends Controller
{
    public function index()
    {
        return view('hasslock');
    }

    public function change_pass(Request $request)
    {
        try {
            $old_password = $request->input('old_password');
            $new_password = $request->input('new_password');
            $confirm_password = $request->input('confirm_password');
            Log::info('Received password change request.', [
                'old_password' => $old_password,
                'new_password' => $new_password,
                'confirm_password' => $confirm_password,
            ]);

            $response = Http::post('http://homeassistant.local:8123/api/change_pass', [
                'old_pass' => $old_password,
                'new_pass' => $new_password,
                'confirm_pass' => $confirm_password,
            ]);

            if($response->status() !== 200)
            {
                $message = $response->json()['error'] ?? 'Unknown error';
                Log::error('Failed to change password via API.', [
                    'status' => $response->status(),
                    'message' => $message,
                ]);
                return response()->json(['error' => 'Failed to change password: ' . $message], $response->status());
            }

            return response()->json(['success' => 'Password changed successfully.']);
        } catch (\Exception $e) {
            Log::error('An error occurred while changing the password.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }
}
