<div>
    @if (session()->has('message'))
        <div>{{ session('message') }}</div>
    @endif

    <a href="{{ route('customers.create') }}">Add New Customer</a>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>GST Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->gst_number }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $customer) }}">Edit</a>
                        <button wire:click="delete({{ $customer->id }})">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>