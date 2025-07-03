<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomerList extends Component
{
    public function delete(Customer $customer)
    {
        $customer->delete();

        session()->flash('message', 'Customer deleted successfully.');
    }

    public function render()
    {
        return view('livewire.customer-list', [
            'customers' => Auth::user()->customers()->get(),
        ])->layout('layouts.app');
    }
}