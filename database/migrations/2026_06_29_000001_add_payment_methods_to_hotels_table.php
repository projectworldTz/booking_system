<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // JSON array of enabled payment method keys, e.g. ["airtel_money","mpesa"]
            // null means all available methods are enabled (safe default for existing hotels)
            $table->json('payment_methods')->nullable()->after('cancellation_policy');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('payment_methods');
        });
    }
};
