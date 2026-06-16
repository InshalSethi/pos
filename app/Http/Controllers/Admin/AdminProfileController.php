<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        
        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
        ]);
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'nullable|required_with:password|string',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $admin->password)) {
                return response()->json([
                    'message' => 'The provided current password does not match our records.'
                ], 422);
            }
            $admin->password = Hash::make($validated['password']);
        }

        $admin->name = $validated['name'];
        $admin->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ]
        ]);
    }
}
