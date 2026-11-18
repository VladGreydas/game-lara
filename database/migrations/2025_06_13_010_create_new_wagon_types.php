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
        Schema::create('passenger_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('explorer_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('scrapper_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('crafter_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->integer('craft_slots')->nullable(false)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafter_wagons');
        Schema::dropIfExists('scrapper_wagons');
        Schema::dropIfExists('explorer_wagons');
        Schema::dropIfExists('passenger_wagons');
    }
};
