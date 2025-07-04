<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_generate_invoice_pdf()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Company',
            'address' => '123 Company St',
            'gst_number' => 'GST12345',
            'pan_number' => 'PAN67890',
            'bank_name' => 'My Bank',
            'account_number' => '123456789',
            'ifsc_code' => 'IFSC0001',
        ]);
        $customer = Customer::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Customer',
            'address' => '456 Customer Ave',
            'gst_number' => 'CUSTGST987',
        ]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'tax_rate' => 10,
            'total_amount' => 110,
        ]);

        $response = $this->actingAs($user)->get(route('invoices.pdf', $invoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}