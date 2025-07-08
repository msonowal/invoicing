<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to drop the companies table with CASCADE
        DB::statement('DROP TABLE IF EXISTS companies CASCADE');

        // Add foreign key constraints to reference teams table
        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('teams')->onDelete('cascade');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily recreate the companies table as data has been migrated
        // This would require restoring the original migration files if needed
    }
};
