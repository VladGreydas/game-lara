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
        // 2024_01_25_143857_create_players_table.php
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable(false)->default('Player');
            $table->integer('money')->nullable(false)->default(100);
            $table->integer('exp')->nullable(false)->default(0);
            $table->integer('max_exp')->nullable(false);
            $table->integer('lvl')->nullable(false);
            $table->timestamps();
        });

        // 2025_06_04_194247_create_cities_table.php (Moved up for dependency resolution)
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('has_workshop')->default(false);
            $table->boolean('has_shop')->default(false);
            $table->timestamps();
        });

        // 2025_06_04_194401_add_city_id_to_players_table.php (Integrated into players table directly)
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('lvl')->constrained()->nullOnDelete();
            // current_location_id and travel_finishes_at will be added after 'locations' table is created
        });

        // 2025_05_28_161654_create_trains_table.php
        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 2025_05_28_170232_create_locomotives_table.php
        Schema::create('locomotives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained('trains')->cascadeOnDelete();
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

        // 2025_05_29_155041_create_wagons_table.php
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

        // 2025_05_29_155320_create_cargo_wagons_table.php
        Schema::create('cargo_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->integer('capacity');
            $table->timestamps();
        });

        // 2025_05_29_155910_create_weapon_wagons_table.php
        Schema::create('weapon_wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained('wagons')->onDelete('cascade');
            $table->integer('slots_available');
            $table->timestamps();
        });

        // 2025_05_29_160212_create_weapons_table.php
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_wagon_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable(false);
            $table->integer('damage')->nullable(false);
            $table->string('type')->nullable(false);
            $table->integer('price')->nullable(false);
            $table->integer('lvl')->nullable(false);
            $table->integer('upgrade_cost')->nullable(false);
            $table->timestamps();
        });

        // 2025_06_04_194809_create_city_routes_table.php
        Schema::create('city_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('to_city_id')->constrained('cities')->cascadeOnDelete();
            $table->unsignedInteger('fuel_cost');
            // NEW: Add travel_time to city_routes
            $table->unsignedInteger('travel_time')->default(1);
            $table->timestamps();
            $table->unique(['from_city_id', 'to_city_id']);
        });

        // 2025_06_11_195924_create_resources_table.php
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('unit')->default('unit');
            $table->timestamps();
        });

        // 2025_06_11_200023_create_cargo_wagon_resources_table.php
        Schema::create('cargo_wagon_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_wagon_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['cargo_wagon_id', 'resource_id']);
        });

        // 2025_06_11_200112_create_city_resources_table.php
        Schema::create('city_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('base_quantity')->default(1000);
            $table->float('price_multiplier')->default(1.0);
            $table->float('buy_price')->default(0);
            $table->float('sell_price')->default(0);
            $table->timestamps();
            $table->unique(['city_id', 'resource_id']);
        });

        // 2025_06_12_182347_create_locations_table.php
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type');
            $table->unsignedInteger('travel_time')->default(1);
            $table->unsignedBigInteger('travel_cost')->default(100);
            $table->timestamps();
        });

        // 2025_06_12_182358_create_location_resources_table.php
        Schema::create('location_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('initial_quantity')->default(0);
            $table->unsignedInteger('current_quantity')->default(0);
            $table->unsignedInteger('regeneration_rate')->default(1);
            $table->unsignedInteger('regeneration_interval')->default(60);
            $table->timestamp('last_regenerated_at')->nullable();
            $table->timestamps();
            $table->unique(['location_id', 'resource_id']);
        });

        // Add new columns to 'players' table related to location and travel
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('current_location_id')->nullable()->after('city_id')->constrained('locations')->onDelete('set null');
            $table->timestamp('travel_starts_at')->nullable()->after('current_location_id');
            $table->timestamp('travel_finishes_at')->nullable()->after('travel_starts_at');
            // NEW: Add current_city_route_id to players
            $table->foreignId('current_city_route_id')->nullable()->after('travel_finishes_at')->constrained('city_routes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation to respect foreign key constraints
        Schema::dropIfExists('location_resources');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('cargo_wagon_resources');
        Schema::dropIfExists('city_resources');
        Schema::dropIfExists('resources');

        // Drop columns from playerі table FIRST that depend on city_routes or locations
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'current_city_route_id')) { // Check if column exists
                $table->dropForeign(['current_city_route_id']);
                $table->dropColumn('current_city_route_id');
            }
            if (Schema::hasColumn('players', 'travel_finishes_at')) {
                $table->dropColumn('travel_finishes_at');
            }
            if (Schema::hasColumn('players', 'current_location_id')) {
                $table->dropForeign(['current_location_id']);
                $table->dropColumn('current_location_id');
            }
        });

        Schema::dropIfExists('city_routes'); // Drop after current_city_route_id is dropped from players
        Schema::dropIfExists('weapons');
        Schema::dropIfExists('weapon_wagons');
        Schema::dropIfExists('cargo_wagons');
        Schema::dropIfExists('wagons');
        Schema::dropIfExists('locomotives');
        Schema::dropIfExists('trains');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('players');
    }
};
