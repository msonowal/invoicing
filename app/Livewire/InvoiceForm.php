<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvoiceForm extends Component
{
    public ?Invoice $invoice = null;

    public $customer_id;
    public $currency;
    public $tax_rate;
    public $line_items = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'currency' => 'required|string|max:3',
        'tax_rate' => 'required|numeric|min:0',
        'line_items.*.description' => 'required|string|max:255',
        'line_items.*.quantity' => 'required|integer|min:1',
        'line_items.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount(?Invoice $invoice)
    {
        if ($invoice->exists) {
            $this->invoice = $invoice;
            $this->customer_id = $invoice->customer_id;
            $this->currency = $invoice->currency;
            $this->tax_rate = $invoice->tax_rate;
            $this->line_items = $invoice->lineItems->toArray();
        } else {
            $this->line_items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
        }
    }

    public function addLineItem()
    {
        $this->line_items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeLineItem($index)
    {
        unset($this->line_items[$index]);
        $this->line_items = array_values($this->line_items);
    }

    public function save()
    {
        $this->validate();

        $totalAmount = 0;
        foreach ($this->line_items as $item) {
            $totalAmount += ($item['quantity'] * $item['unit_price']);
        }
        $totalAmount += ($totalAmount * ($this->tax_rate / 100));

        $invoice = Auth::user()->invoices()->updateOrCreate(
            ['id' => $this->invoice?->id],
            [
                'customer_id' => $this->customer_id,
                'currency' => $this->currency,
                'tax_rate' => $this->tax_rate,
                'total_amount' => $totalAmount,
            ]
        );

        $invoice->lineItems()->delete();
        foreach ($this->line_items as $item) {
            $invoice->lineItems()->create($item);
        }

        session()->flash('message', 'Invoice saved successfully.');

        return $this->redirectRoute('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoice-form', [
            'customers' => Auth::user()->customers,
        ])->layout('layouts.app');
    }
}