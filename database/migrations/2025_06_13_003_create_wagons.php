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
        Schema::create('wagons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false)->default('Wagon');
            $table->foreignId('train_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('weight')->nullable(false);
            $table->integer('armor')->nullable(false);
            $table->integer('max_armor')->nullable(false);
            $table->integer('price')->nullable(false);
            $table->integer('lvl')->nullable(false)->default(1);
            $table->integer('upgrade_cost')->nullable(false)->default(100);
            $table->timestamps();
        });

        Schema::create('cargo_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->integer('capacity');
            $table->timestamps();
        });

        Schema::create('weapon_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->integer('slots_available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weapon_wagons');
        Schema::dropIfExists('cargo_wagons');
        Schema::dropIfExists('wagons');
    }
};
