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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('tier')->default('0');
            $table->string('produced_from')->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->default('unit');
            $table->boolean('is_fuel')->default(false);
            $table->unsignedInteger('fuel_value')->default(0);
            $table->integer('base_price')->nullable(false)->default(10);
            $table->timestamps();
        });

        Schema::create('city_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('base_quantity')->default(1000);
            $table->float('price_multiplier')->default(1.0);
            $table->float('buy_price')->default(0);
            $table->float('sell_price')->default(0);
            $table->boolean('is_surplus')->default(false);
            $table->boolean('is_deficit')->default(false);
            $table->unsignedSmallInteger('level')->default(1);
            $table->timestamps();
            $table->unique(['city_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_resources');
        Schema::dropIfExists('resources');
    }
};
