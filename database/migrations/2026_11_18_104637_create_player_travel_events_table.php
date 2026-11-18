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
        Schema::create('player_travel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->dateTime('travel_start_at');
            $table->tinyInteger('event_progress'); // 20, 40, 60, or 80
            $table->string('event_slug');
            $table->dateTime('original_travel_finishes_at'); // Original arrival time before event pause
            $table->integer('time_delay_seconds')->default(0); // Additional time from event effects
            $table->dateTime('triggered_at');
            $table->dateTime('resolved_at')->nullable();
            $table->string('choice_made')->nullable();
            $table->json('effects_applied')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'travel_start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_travel_events');
    }
};
