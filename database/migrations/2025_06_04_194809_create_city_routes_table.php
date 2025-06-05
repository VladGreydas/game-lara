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
        Schema::create('city_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('to_city_id')->constrained('cities')->cascadeOnDelete();
            $table->unsignedInteger('fuel_cost');
            $table->timestamps();
            $table->unique(['from_city_id', 'to_city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_routes');
    }
};
