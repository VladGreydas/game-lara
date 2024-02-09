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
        Schema::create('locomotives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable(false)->default('Steamy Joe');
            $table->integer('weight')->nullable(false)->default(350);
            $table->string('type')->nullable(false)->default('Steam');
            $table->integer('power')->nullable(false)->default(2500);
            $table->integer('armor')->nullable(false)->default(500);
            $table->integer('max_armor')->nullable(false)->default(500);
            $table->integer('fuel')->nullable(false)->default(10);
            $table->integer('max_fuel')->nullable(false)->default(10);
            $table->integer('price')->nullable(false)->default(500);
            $table->integer('lvl')->nullable(false)->default(1);
            $table->integer('upgrade_cost')->nullable(false)->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locomotives');
    }
};
