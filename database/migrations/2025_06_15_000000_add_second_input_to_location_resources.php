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
        Schema::table('location_resources', function (Blueprint $table) {
            $table->foreignId('input_resource_2_id')->nullable()->after('input_resource_id')->constrained('resources')->onDelete('set null');
            $table->integer('processing_ratio_input2')->default(1)->after('input_resource_2_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_resources', function (Blueprint $table) {
            $table->dropForeign(['input_resource_2_id']);
            $table->dropColumn(['input_resource_2_id', 'processing_ratio_input2']);
        });
    }
};
