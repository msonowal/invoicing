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
        // Update customers table: organization_id should reference team_id from companies
        DB::statement('UPDATE customers SET organization_id = (SELECT team_id FROM companies WHERE companies.id = customers.organization_id)');

        // First, update organization_location_id in invoices to reference the same location (before changing organization_id)
        DB::statement('UPDATE invoices SET organization_location_id = (SELECT primary_location_id FROM companies WHERE companies.id = invoices.organization_id)');

        // Then update invoices table: organization_id should reference team_id from companies
        DB::statement('UPDATE invoices SET organization_id = (SELECT team_id FROM companies WHERE companies.id = invoices.organization_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible since we're changing the reference structure
        // In a real scenario, you'd need to store the original company_id mapping
    }
};
