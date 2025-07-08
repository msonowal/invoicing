<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy company data to teams
        $companies = DB::table('companies')->get();

        foreach ($companies as $company) {
            DB::table('teams')
                ->where('id', $company->team_id)
                ->update([
                    'phone' => $company->phone,
                    'emails' => $company->emails,
                    'primary_location_id' => $company->primary_location_id,
                    'currency' => $company->currency,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove business data from teams
        DB::table('teams')->update([
            'phone' => null,
            'emails' => null,
            'primary_location_id' => null,
            'currency' => 'INR',
        ]);
    }
};
