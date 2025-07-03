<?php

namespace App\Livewire;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvoiceList extends Component
{
    public function delete(Invoice $invoice)
    {
        $invoice->delete();

        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function render()
    {
        return view('livewire.invoice-list', [
            'invoices' => Auth::user()->invoices()->get(),
        ])->layout('layouts.app');
    }
}