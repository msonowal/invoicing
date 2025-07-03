<?php

namespace Tests\Feature;

use App\Livewire\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_company_profile_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get('/profile')->assertStatus(200);
    }

    /** @test */
    public function can_update_company_profile()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CompanyProfile::class)
            ->set('name', 'Test Company')
            ->set('address', '123 Test Street')
            ->set('gst_number', '1234567890')
            ->set('pan_number', 'ABCDE1234F')
            ->set('bank_name', 'Test Bank')
            ->set('account_number', '1234567890')
            ->set('ifsc_code', 'TEST0000001')
            ->call('save');

        $user->refresh();

        $this->assertEquals('Test Company', $user->company->name);
        $this->assertEquals('123 Test Street', $user->company->address);
        $this->assertEquals('1234567890', $user->company->gst_number);
        $this->assertEquals('ABCDE1234F', $user->company->pan_number);
        $this->assertEquals('Test Bank', $user->company->bank_name);
        $this->assertEquals('1234567890', $user->company->account_number);
        $this->assertEquals('TEST0000001', $user->company->ifsc_code);
    }
}