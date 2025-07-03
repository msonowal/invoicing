<div>
    <form wire:submit.prevent="save">
        <div>
            <label for="name">Customer Name</label>
            <input type="text" id="name" wire:model="name">
            @error('name') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="address">Address</label>
            <input type="text" id="address" wire:model="address">
            @error('address') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="gst_number">GST Number</label>
            <input type="text" id="gst_number" wire:model="gst_number">
            @error('gst_number') <span>{{ $message }}</span> @enderror
        </div>

        <button type="submit">Save Customer</button>

        @if (session()->has('message'))
            <div>{{ session('message') }}</div>
        @endif
    </form>
</div>