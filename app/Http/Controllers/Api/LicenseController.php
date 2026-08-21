<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class LicenseController extends Controller
{
    // Check if system is activated
    public function checkStatus(Request $request)
    {
        $user = $request->user() ?: \Illuminate\Support\Facades\Auth::user();
        $license = License::first();

        // Only create initial license if no record exists in database
        if (!$license) {
            $license = License::create([
                'id'             => 1,
                'license_key'    => 'DEMO-ELITE-YEARLY',
                'device_id'     => 'DEFAULT-DEVICE',
                'plan'          => 'elite',
                'status'        => 'active',
                'start_date'    => now()->toDateString(),
                'expires_at'    => now()->addYear()->toDateString(),
                'last_opened_at' => now(),
            ]);
        } else {
            $license->update(['last_opened_at' => now()]);
        }

        $admin = $user ?: \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'owner')->orWhere('name', 'admin');
        })->first() ?: \App\Models\User::first();

        $status = $license->status ?: 'active';

        // Logged-in users are always active without mutating DB license plan/expiry
        if ($user) {
            $status = 'active';
        }

        return response()->json([
            'status'      => $status,
            'license'     => $license,
            'admin_name'  => $admin ? $admin->name : 'Administrator',
            'admin_email' => $admin ? $admin->email : 'admin@example.com',
        ]);
    }

    // Activate License by calling the Live Server
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string'
        ]);

        try {
            // Simulated call to Live Server (replace with actual URL)
            // $response = Http::post('https://your-live-site.com/api/licenses/verify', [
            //     'license_key' => $request->license_key,
            //     'device_id' => $request->device_id,
            // ]);
            
            // For now, we mock the Live Server response
            // We assume the live server validates and sends back expiration info
            $mockLiveResponse = [
                'status' => 'success',
                'data' => [
                    'license_key' => $request->license_key,
                    'plan' => 'basic',
                    'start_date' => now()->toDateString(),
                    'expires_at' => now()->addDays(30)->toDateString(),
                ]
            ];
            
            if ($mockLiveResponse['status'] !== 'success') {
                return response()->json(['message' => 'Invalid License Key or Device Limit Reached.'], 400);
            }

            // Wipe existing database and set up fresh for new license (optional, depending on business rules)
            if (!License::exists()) {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            }

            $data = $mockLiveResponse['data'];

            $license = License::updateOrCreate(
                ['id' => 1], // Singleton license row
                [
                    'license_key' => $data['license_key'],
                    'device_id' => $request->device_id,
                    'plan' => $data['plan'],
                    'status' => 'active',
                    'start_date' => $data['start_date'],
                    'expires_at' => $data['expires_at'],
                    'last_opened_at' => now(),
                ]
            );

            return response()->json([
                'message' => 'Software activated successfully.',
                'license' => $license
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to connect to licensing server.', 'error' => $e->getMessage()], 500);
        }
    }

    public function renew(Request $request)
    {
        $license = License::first();
        if ($license) {
            $license->update([
                'status' => 'active',
                'expires_at' => now()->addYear()
            ]);
            return response()->json(['message' => 'License renewed successfully', 'license' => $license]);
        }
        return response()->json(['message' => 'License not found'], 404);
    }
}
