<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Add business fields from companies table
            $table->string('phone')->nullable()->after('custom_domain');
            $table->json('emails')->nullable()->after('phone');
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->after('emails');
            $table->string('currency', 3)->default('INR')->after('primary_location_id');

            // Add indexes for performance
            $table->index(['currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['currency']);
            $table->dropForeign(['primary_location_id']);
            $table->dropColumn(['phone', 'emails', 'primary_location_id', 'currency']);
        });
    }
};
