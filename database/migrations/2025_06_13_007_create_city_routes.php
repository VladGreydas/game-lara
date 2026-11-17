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
            $table->foreignId('from_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('type'); // 'city_to_city', 'city_to_location', 'location_to_city'
            $table->unsignedInteger('distance_km')->nullable(false)->default(100);
            $table->timestamps();

            $table->unique(['from_city_id', 'to_city_id', 'type']);
            $table->unique(['from_location_id', 'to_city_id', 'type']);
            $table->unique(['from_city_id', 'to_location_id', 'type']);
        });

        // Add travel-related columns to players table
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('current_location_id')->nullable()->after('city_id')->constrained('locations')->onDelete('set null');
            $table->timestamp('travel_starts_at')->nullable()->after('current_location_id');
            $table->timestamp('travel_finishes_at')->nullable()->after('travel_starts_at');
            $table->foreignId('current_city_route_id')->nullable()->after('travel_finishes_at')->constrained('city_routes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'current_city_route_id')) {
                $table->dropForeign(['current_city_route_id']);
                $table->dropColumn('current_city_route_id');
            }
            if (Schema::hasColumn('players', 'travel_finishes_at')) {
                $table->dropColumn('travel_finishes_at');
            }
            if (Schema::hasColumn('players', 'travel_starts_at')) {
                $table->dropColumn('travel_starts_at');
            }
            if (Schema::hasColumn('players', 'current_location_id')) {
                $table->dropForeign(['current_location_id']);
                $table->dropColumn('current_location_id');
            }
        });

        Schema::dropIfExists('city_routes');
    }
};
