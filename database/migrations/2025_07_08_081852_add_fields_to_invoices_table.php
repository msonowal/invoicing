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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('total');
            $table->text('notes')->nullable()->after('subject');
            $table->integer('adjustment')->default(0)->after('notes');
            $table->unsignedSmallInteger('tds')->nullable()->after('adjustment');
            $table->unsignedSmallInteger('tcs')->nullable()->after('tds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subject', 'notes', 'adjustment', 'tds', 'tcs']);
        });
    }
};
