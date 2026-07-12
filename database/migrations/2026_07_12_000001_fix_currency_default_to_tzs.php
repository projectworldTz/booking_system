<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'invoices', 'transactions', 'payments'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('currency', 3)->default('TZS')->change();
            });

            DB::table($table)->where('currency', 'USD')->update(['currency' => 'TZS']);
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'invoices', 'transactions', 'payments'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('currency', 3)->default('USD')->change();
            });
        }
    }
};
