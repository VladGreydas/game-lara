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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wagons');
    }
};
