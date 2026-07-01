<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_key');
            $table->date('visited_on');
            $table->timestamps();

            $table->unique(['hotel_id', 'visitor_key', 'visited_on']);
            $table->index(['hotel_id', 'visited_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_visits');
    }
};
