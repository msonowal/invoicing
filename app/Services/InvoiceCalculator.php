<?php

namespace App\Services;

use App\Models\Invoice;
use App\ValueObjects\InvoiceTotals;
use Illuminate\Support\Collection;

class InvoiceCalculator
{
    public function calculateInvoice(Invoice $invoice): InvoiceTotals
    {
        $items = $invoice->items;

        if ($items->isEmpty()) {
            return InvoiceTotals::zero();
        }

        return $this->calculateFromItems($items, $invoice->adjustment, $invoice->tds, $invoice->tcs);
    }

    public function calculateFromItems(Collection $items, int $adjustment = 0, ?float $tds = null, ?float $tcs = null): InvoiceTotals
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $lineTotal = $item->getLineTotal() - $item->discount;
            $lineTax = $item->getTaxAmount();

            $subtotal += $lineTotal;
            $taxAmount += $lineTax;
        }

        $total = $subtotal + $taxAmount + $adjustment;

        if ($tds !== null) {
            $tdsAmount = (int) round(($total * ($tds * 100)) / 10000);
            $total -= $tdsAmount;
        }

        if ($tcs !== null) {
            $tcsAmount = (int) round(($total * ($tcs * 100)) / 10000);
            $total += $tcsAmount;
        }

        return new InvoiceTotals(
            subtotal: $subtotal,
            tax: $taxAmount,
            total: $total
        );
    }

    public function updateInvoiceTotals(Invoice $invoice): Invoice
    {
        $totals = $this->calculateInvoice($invoice);

        $invoice->subtotal = $totals->subtotal;
        $invoice->tax = $totals->tax;
        $invoice->total = $totals->total;

        return $invoice;
    }

    public function recalculateInvoice(Invoice $invoice): Invoice
    {
        $invoice->refresh();
        $invoice->load('items');

        return $this->updateInvoiceTotals($invoice);
    }
}
