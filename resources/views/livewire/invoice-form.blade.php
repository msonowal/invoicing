<div>
    <form wire:submit.prevent="save">
        <div>
            <label for="customer_id">Customer</label>
            <select id="customer_id" wire:model="customer_id">
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="currency">Currency</label>
            <input type="text" id="currency" wire:model="currency">
            @error('currency') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="tax_rate">TAX Rate (%)</label>
            <input type="number" id="tax_rate" wire:model="tax_rate">
            @error('tax_rate') <span>{{ $message }}</span> @enderror
        </div>

        <h3>Line Items</h3>
        @foreach ($line_items as $index => $item)
            <div>
                <label for="description-{{ $index }}">Description</label>
                <input type="text" id="description-{{ $index }}" wire:model="line_items.{{ $index }}.description">
                @error('line_items.' . $index . '.description') <span>{{ $message }}</span> @enderror

                <label for="quantity-{{ $index }}">Quantity</label>
                <input type="number" id="quantity-{{ $index }}" wire:model="line_items.{{ $index }}.quantity">
                @error('line_items.' . $index . '.quantity') <span>{{ $message }}</span> @enderror

                <label for="unit_price-{{ $index }}">Unit Price</label>
                <input type="number" id="unit_price-{{ $index }}" wire:model="line_items.{{ $index }}.unit_price">
                @error('line_items.' . $index . '.unit_price') <span>{{ $message }}</span> @enderror

                <button type="button" wire:click="removeLineItem({{ $index }})">Remove</button>
            </div>
        @endforeach
        <button type="button" wire:click="addLineItem">Add Line Item</button>

        <button type="submit">Save Invoice</button>

        @if (session()->has('message'))
            <div>{{ session('message') }}</div>
        @endif
    </form>
</div>