<?php

namespace Tests\Feature;

use App\Livewire\InvoiceForm;
use App\Livewire\InvoiceList;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_invoice_listing_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/invoices')->assertStatus(200);
    }

    /** @test */
    public function can_create_new_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(InvoiceForm::class)
            ->set('customer_id', $customer->id)
            ->set('currency', 'USD')
            ->set('tax_rate', 10)
            ->set('line_items', [
                ['description' => 'Item 1', 'quantity' => 1, 'unit_price' => 100],
                ['description' => 'Item 2', 'quantity' => 2, 'unit_price' => 50],
            ])
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'tax_rate' => 10,
        ]);

        $invoice = Invoice::where('user_id', $user->id)->first();
        $this->assertCount(2, $invoice->lineItems);
        $this->assertEquals(220, $invoice->total_amount);
    }

    /** @test */
    public function can_update_existing_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id]);

        Livewire::actingAs($user)
            ->test(InvoiceForm::class, ['invoice' => $invoice])
            ->set('currency', 'EUR')
            ->set('tax_rate', 15)
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'currency' => 'EUR',
            'tax_rate' => 15,
        ]);
    }

    /** @test */
    public function can_delete_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id]);

        Livewire::actingAs($user)
            ->test(InvoiceList::class)
            ->call('delete', $invoice->id);

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id,
        ]);
    }
}