<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySwitcherController extends Controller
{
    /**
     * Get all companies the authenticated user belongs to.
     */
    public function index()
    {
        $user = Auth::user();
        $companies = $user->companies()->get();
        return response()->json([
            'companies' => $companies,
            'active_company_id' => $user->current_company_id,
        ]);
    }

    /**
     * Switch the active company.
     */
    public function switchCompany(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = Auth::user();

        // Verify user has access to this company
        if (!$user->companies()->where('company_id', $request->company_id)->exists()) {
            return response()->json(['message' => 'Unauthorized access to this company.'], 403);
        }

        // Update active company
        $user->current_company_id = $request->company_id;
        $user->save();

        // Clear necessary session data if any (e.g. app_currency)
        $company = $user->companies()->where('company_id', $request->company_id)->first();
        session(['app_currency' => $company->base_currency]);

        return response()->json([
            'message' => 'Company switched successfully.',
            'company' => $company
        ]);
    }

    /**
     * Get the active company details.
     */
    public function getActiveCompany()
    {
        $user = Auth::user();
        if (!$user->current_company_id) {
            return response()->json(['message' => 'No active company found.'], 404);
        }
        
        $company = $user->currentCompany;
        return response()->json(['company' => $company]);
    }

    /**
     * Update the active company details.
     */
    public function updateActiveCompany(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'business_type' => 'nullable|string|max:100',
            'business_scale' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'system_language' => 'nullable|string|max:10',
            'base_currency' => 'nullable|string|max:10',
            'timezone_offset' => 'nullable|string|max:50',
        ]);

        if (!$user->current_company_id) {
            $validatedData['user_id'] = $user->id;
            // Provide defaults for strictly required columns not present in settings form
            $defaults = [
                'owner_role' => 'Owner',
                'team_size' => '1',
                'intended_tasks' => json_encode([]),
                'business_scale' => 'Single Outlet',
                'system_language' => 'en',
                'base_currency' => 'USD',
                'timezone_offset' => 'UTC',
                'fiscal_year_start' => date('Y-01-01'),
            ];
            $company = \App\Models\Company::create(array_merge($defaults, $validatedData));
            
            $user->current_company_id = $company->id;
            $user->save();
            
            if (!$user->companies()->where('company_id', $company->id)->exists()) {
                $user->companies()->attach($company->id, ['role' => 'owner']);
            }
        } else {
            $company = $user->currentCompany;
            $company->update($validatedData);
        }

        return response()->json([
            'message' => 'Company details updated successfully.',
            'company' => $company
        ]);
    }
}
