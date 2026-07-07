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
        Schema::table('hotels', function (Blueprint $table) {
            // Distinct from online_booking_enabled (which blocks booking entirely) —
            // this only swaps the payment step for a manual-numbers fallback.
            $table->boolean('manual_payment_enabled')->default(false)->after('online_booking_enabled');
            $table->json('manual_payment_numbers')->nullable()->after('manual_payment_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['manual_payment_enabled', 'manual_payment_numbers']);
        });
    }
};
