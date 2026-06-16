<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_admins' => \App\Models\Admin::count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_month' => User::whereMonth('created_at', now()->month)->count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}
