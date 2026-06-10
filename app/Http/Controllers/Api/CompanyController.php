<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $companies = $user->companies()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', '%' . $search . '%')
                        ->orWhere('tax_number', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            })
            ->paginate(25);

        $drafts = Company::where('user_id', $user->id)
            ->where('status', 'draft')
            ->latest()
            ->get();

        return response()->json([
            'companies' => $companies,
            'drafts' => $drafts,
            'current_company_id' => $user->current_company_id
        ]);
    }

    public function switch(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->companies()->where('company_id', $id)->exists()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $user->current_company_id = $id;
        $user->save();

        $company = $user->companies()->where('company_id', $id)->first();
        if ($company) {
            session(['app_currency' => $company->base_currency]);
            \Illuminate\Support\Facades\Cache::forever('system_timezone', $company->timezone_offset);
            \Illuminate\Support\Facades\Config::set('app.timezone', $company->timezone_offset);
        }

        return response()->json(['message' => 'Active workspace switched successfully.']);
    }

    public function createNewCompany()
    {
        $user = Auth::user();
        $user->onboarding_completed = 0;
        $user->save();

        return response()->json(['message' => 'Redirecting to onboarding...']);
    }

    public function show($id)
    {
        $user = Auth::user();
        $company = $user->companies()->findOrFail($id);
        return response()->json($company);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->companies()->findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company_logos', 'public');
            $validated['company_logo'] = $path;
        }

        $company->update($validated);

        return response()->json(['message' => 'Company updated successfully', 'company' => $company]);
    }
}
