<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminSettingsController extends Controller
{
    private $settingsPath;

    public function __construct()
    {
        $this->settingsPath = storage_path('app/admin_settings.json');
    }

    public function show()
    {
        $defaultSettings = [
            'system_name' => 'Admin System',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'session_timeout' => 120,
        ];

        if (File::exists($this->settingsPath)) {
            $savedSettings = json_decode(File::get($this->settingsPath), true);
            $settings = array_merge($defaultSettings, $savedSettings ?? []);
        } else {
            $settings = $defaultSettings;
        }

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'session_timeout' => 'required|integer|min:5|max:1440',
        ]);

        // Merge with existing to ensure no lost settings if we expand later
        $settings = [];
        if (File::exists($this->settingsPath)) {
            $settings = json_decode(File::get($this->settingsPath), true) ?? [];
        }

        $settings = array_merge($settings, $validated);

        File::put($this->settingsPath, json_encode($settings, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => 'Settings updated successfully.',
            'settings' => $settings
        ]);
    }
}
