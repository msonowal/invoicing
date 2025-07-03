<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CompanyProfile extends Component
{
    public $name;
    public $address;
    public $gst_number;
    public $pan_number;
    public $bank_name;
    public $account_number;
    public $ifsc_code;

    public function mount()
    {
        $company = Auth::user()->company;

        if ($company) {
            $this->name = $company->name;
            $this->address = $company->address;
            $this->gst_number = $company->gst_number;
            $this->pan_number = $company->pan_number;
            $this->bank_name = $company->bank_name;
            $this->account_number = $company->account_number;
            $this->ifsc_code = $company->ifsc_code;
        }
    }

    public function save()
    {
        $user = Auth::user();

        $user->company()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $this->name,
                'address' => $this->address,
                'gst_number' => $this->gst_number,
                'pan_number' => $this->pan_number,
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'ifsc_code' => $this->ifsc_code,
            ]
        );

        session()->flash('message', 'Profile successfully updated.');
    }

    public function render()
    {
        return view('livewire.company-profile');
    }
}