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

        return response()->json(['message' => 'Active workspace switched successfully.']);
    }

    public function createNewCompany()
    {
        $user = Auth::user();
        $user->onboarding_completed = 0;
        $user->save();

        return response()->json(['message' => 'Redirecting to onboarding...']);
    }
}
