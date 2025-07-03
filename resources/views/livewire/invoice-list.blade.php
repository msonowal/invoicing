<div>
    @if (session()->has('message'))
        <div>{{ session('message') }}</div>
    @endif

    <a href="{{ route('invoices.create') }}">Create New Invoice</a>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Currency</th>
                <th>TAX Rate</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->customer->name }}</td>
                    <td>{{ $invoice->currency }}</td>
                    <td>{{ $invoice->tax_rate }}%</td>
                    <td>{{ $invoice->total_amount }}</td>
                    <td>
                        <a href="{{ route('invoices.edit', $invoice) }}">Edit</a>
                        <button wire:click="delete({{ $invoice->id }})">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>