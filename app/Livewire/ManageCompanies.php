<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ManageCompanies extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createNewCompany()
    {
        $user = Auth::user();
        $user->onboarding_completed = 0;
        $user->save();

        session()->forget('onboarding_step');

        return redirect()->route('company.setup');
    }

    public function switchCompany($companyId)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($user->companies()->where('company_id', $companyId)->exists()) {
            $user->current_company_id = $companyId;
            $user->save();
            
            session()->flash('success', 'Active workspace switched successfully.');
            return redirect()->route('companies');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $companies = $user->companies()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('company_name', 'like', '%' . $this->search . '%')
                      ->orWhere('tax_number', 'like', '%' . $this->search . '%')
                      ->orWhere('registration_number', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(25);

        return view('livewire.manage-companies', [
            'companies' => $companies
        ])->layout('components.layouts.app');
    }
}
