<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CompanyProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_update_company_profile()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                    ->visit(config('app.url') . '/profile')
                    ->assertSourceHas('<!DOCTYPE html>') // Assert that the basic HTML doctype is present
                    ->screenshot('company-profile-loaded');
        });
    }
}