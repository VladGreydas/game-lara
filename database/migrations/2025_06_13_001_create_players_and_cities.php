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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable(false)->default('Player');
            $table->integer('money')->nullable(false)->default(1000);
            $table->integer('exp')->nullable(false)->default(0);
            $table->integer('max_exp')->nullable(false)->default(100);
            $table->integer('lvl')->nullable(false)->default(1);
            $table->integer('stamina')->default(100);
            $table->integer('max_stamina')->default(100);
            $table->timestamp('last_stamina_regenerated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('has_workshop')->default(false);
            $table->boolean('has_shop')->default(false);
            $table->boolean('has_saloon')->default(false);
            $table->unsignedSmallInteger('level')->default(1);
            $table->unsignedSmallInteger('max_level')->default(10);
            $table->timestamps();
        });

        // Add city_id to players after cities table is created
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('lvl')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'city_id')) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            }
        });

        Schema::dropIfExists('cities');
        Schema::dropIfExists('players');
    }
};
