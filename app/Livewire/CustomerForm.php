<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?Customer $customer = null;

    public $name;
    public $address;
    public $gst_number;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'gst_number' => 'required|string|max:255',
    ];

    public function mount(?Customer $customer)
    {
        if ($customer->exists) {
            $this->customer = $customer;
            $this->name = $customer->name;
            $this->address = $customer->address;
            $this->gst_number = $customer->gst_number;
        }
    }

    public function save()
    {
        $this->validate();

        Auth::user()->customers()->updateOrCreate(
            ['id' => $this->customer?->id],
            [
                'name' => $this->name,
                'address' => $this->address,
                'gst_number' => $this->gst_number,
            ]
        );

        session()->flash('message', 'Customer saved successfully.');

        return $this->redirectRoute('customers.index');
    }

    public function render()
    {
        return view('livewire.customer-form')->layout('layouts.app');
    }
}