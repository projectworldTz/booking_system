<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('feature');                           // Feature enum value
            $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();         // null = permanent
            $table->text('notes')->nullable();                   // agreement ref or internal note
            $table->timestamps();

            $table->unique(['hotel_id', 'feature']);
            $table->index(['hotel_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_features');
    }
};
