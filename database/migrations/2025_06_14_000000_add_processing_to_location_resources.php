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
            $table->foreignId('input_resource_id')->nullable()->after('resource_id')->constrained('resources')->onDelete('set null');
            $table->integer('processing_ratio_input')->default(1)->after('input_resource_id');
            $table->integer('processing_ratio_output')->default(1)->after('processing_ratio_input');
            $table->integer('processing_amount')->default(0)->after('processing_ratio_output');
            $table->timestamp('processing_started_at')->nullable()->after('processing_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_resources', function (Blueprint $table) {
            $table->dropForeign(['input_resource_id']);
            $table->dropColumn(['input_resource_id', 'processing_ratio_input', 'processing_ratio_output', 'processing_amount', 'processing_started_at']);
        });
    }
};
