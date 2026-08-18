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
        
        $nameParts = explode(' ', trim($admin->name));
        $firstName = array_shift($nameParts) ?: $admin->name;
        $lastName = count($nameParts) > 0 ? array_pop($nameParts) : '';
        $middleName = count($nameParts) > 0 ? implode(' ', $nameParts) : '';

        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'email' => $admin->email,
        ]);
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'current_password' => 'nullable|required_with:password|string',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $firstName = trim($request->input('first_name', ''));
        $middleName = trim($request->input('middle_name', ''));
        $lastName = trim($request->input('last_name', ''));

        if (!$firstName && $request->has('name')) {
            $nameParts = explode(' ', trim($request->name));
            $firstName = array_shift($nameParts) ?: $request->name;
            $lastName = count($nameParts) > 0 ? array_pop($nameParts) : '';
            $middleName = count($nameParts) > 0 ? implode(' ', $nameParts) : '';
        }

        $fullName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $admin->password)) {
                return response()->json([
                    'message' => 'The provided current password does not match our records.'
                ], 422);
            }
            $admin->password = Hash::make($validated['password']);
        }

        $admin->name = $fullName ?: $admin->name;
        $admin->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'email' => $admin->email,
            ]
        ]);
    }
}
