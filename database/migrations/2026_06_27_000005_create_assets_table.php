<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->restrictOnDelete();

            $table->string('name');
            $table->string('asset_code', 30); // e.g. FURN-001, ELEC-004
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // Room 101, Lobby, Restaurant, Storage

            $table->unsignedSmallInteger('quantity')->default(1);

            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])
                  ->default('good');

            $table->enum('status', ['active', 'under_maintenance', 'disposed'])
                  ->default('active');

            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->date('last_serviced_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['hotel_id', 'asset_code']);
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'asset_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
