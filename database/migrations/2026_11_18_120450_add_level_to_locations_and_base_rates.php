<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->integer('lvl')->default(1)->after('slug');
        });

        Schema::table('location_resources', function (Blueprint $table) {
            $table->float('base_production_rate')->default(0)->after('resource_id');
            $table->integer('base_max_capacity')->default(0)->after('base_production_rate');
        });

        // Migrate existing data: production_rate_per_hour → base_production_rate, max_capacity → base_max_capacity
        DB::table('location_resources')->update([
            'base_production_rate' => DB::raw('production_rate_per_hour'),
            'base_max_capacity' => DB::raw('max_capacity'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_resources', function (Blueprint $table) {
            $table->dropColumn(['base_production_rate', 'base_max_capacity']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('lvl');
        });
    }
};
