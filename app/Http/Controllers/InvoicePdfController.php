<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function generatePdf(Invoice $invoice)
    {
        $user = Auth::user();
        $company = $user->company;
        $customer = $invoice->customer;
        $lineItems = $invoice->lineItems;

        $data = [
            'invoice' => $invoice,
            'company' => $company,
            'customer' => $customer,
            'lineItems' => $lineItems,
        ];

        $pdf = Pdf::loadView('invoice.pdf', $data);

        return $pdf->download('invoice-' . $invoice->id . '.pdf');
    }
}
