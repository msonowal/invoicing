<div>
    <form wire:submit.prevent="save">
        <div>
            <label for="name">Company Name</label>
            <input type="text" id="name" wire:model="name">
        </div>

        <div>
            <label for="address">Address</label>
            <input type="text" id="address" wire:model="address">
        </div>

        <div>
            <label for="gst_number">GST Number</label>
            <input type="text" id="gst_number" wire:model="gst_number">
        </div>

        <div>
            <label for="pan_number">PAN Number</label>
            <input type="text" id="pan_number" wire:model="pan_number">
        </div>

        <div>
            <label for="bank_name">Bank Name</label>
            <input type="text" id="bank_name" wire:model="bank_name">
        </div>

        <div>
            <label for="account_number">Account Number</label>
            <input type="text" id="account_number" wire:model="account_number">
        </div>

        <div>
            <label for="ifsc_code">IFSC Code</label>
            <input type="text" id="ifsc_code" wire:model="ifsc_code">
        </div>

        <button type="submit">Save</button>

        @if (session()->has('message'))
            <div>{{ session('message') }}</div>
        @endif
    </form>
</div>