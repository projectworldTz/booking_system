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
            $table->decimal('cancellation_deduction', 10, 2)->nullable()->after('grand_total');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('cancellation_deduction');
            $table->decimal('deduction_percentage', 5, 2)->nullable()->after('refund_amount');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['cancellation_deduction', 'refund_amount', 'deduction_percentage', 'cancelled_at']);
        });
    }
};
