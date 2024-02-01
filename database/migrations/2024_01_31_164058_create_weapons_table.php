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
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_wagon_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable(false);
            $table->integer('damage')->nullable(false);
            $table->string('type')->nullable(false);
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
        Schema::dropIfExists('weapons');
    }
};
