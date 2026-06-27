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
        Schema::create('cancellation_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users'); // receptionist
            $table->foreignId('approved_by')->nullable()->constrained('users'); // owner

            $table->enum('status', ['pending', 'approved', 'denied', 'executed'])->default('pending');

            $table->text('reason');
            $table->text('denial_reason')->nullable();

            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('deduction_percentage', 5, 2)->default(60.00);
            $table->decimal('refund_percentage', 5, 2)->default(40.00);
            $table->decimal('deduction_amount', 10, 2)->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_approvals');
    }
};
