<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration // <--- Change "class Venue extends Migration" to this
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('capacity');
            $table->decimal('price_per_hour', 10, 2);
            $table->string('image')->nullable(); // Main cover image
            $table->json('showcase_images')->nullable(); // Showcase gallery images array
            $table->json('features')->nullable(); // Key features array
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
