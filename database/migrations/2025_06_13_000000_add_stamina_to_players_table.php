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
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'stamina')) {
                $table->integer('stamina')->default(100)->after('lvl');
            }
            if (!Schema::hasColumn('players', 'max_stamina')) {
                $table->integer('max_stamina')->default(100)->after('stamina');
            }
            if (!Schema::hasColumn('players', 'last_stamina_regenerated_at')) {
                $table->timestamp('last_stamina_regenerated_at')->nullable()->after('max_stamina');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'last_stamina_regenerated_at')) {
                $table->dropColumn('last_stamina_regenerated_at');
            }
            if (Schema::hasColumn('players', 'max_stamina')) {
                $table->dropColumn('max_stamina');
            }
            if (Schema::hasColumn('players', 'stamina')) {
                $table->dropColumn('stamina');
            }
        });
    }
};
