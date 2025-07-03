<?php

namespace Tests\Feature;

use App\Livewire\CustomerForm;
use App\Livewire\CustomerList;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_customer_listing_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/customers')->assertStatus(200);
    }

    /** @test */
    public function can_create_new_customer()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CustomerForm::class)
            ->set('name', 'New Customer')
            ->set('address', '456 New Street')
            ->set('gst_number', '0987654321')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'name' => 'New Customer',
            'address' => '456 New Street',
            'gst_number' => '0987654321',
        ]);
    }

    /** @test */
    public function can_update_existing_customer()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(CustomerForm::class, ['customer' => $customer])
            ->set('name', 'Updated Customer Name')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
        ]);
    }

    /** @test */
    public function can_delete_customer()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(CustomerList::class)
            ->call('delete', $customer->id);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}