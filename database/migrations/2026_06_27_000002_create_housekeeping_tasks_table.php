<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', [
                'checkout_cleaning',
                'routine_cleaning',
                'deep_clean',
                'turndown',
            ])->default('routine_cleaning');

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'inspected',
            ])->default('pending');

            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');

            $table->text('notes')->nullable();
            $table->text('inspector_notes')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
