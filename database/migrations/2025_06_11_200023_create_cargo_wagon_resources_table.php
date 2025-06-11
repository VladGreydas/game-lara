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
        Schema::create('cargo_wagon_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_wagon_id')->constrained()->onDelete('cascade'); // Зв'язок з CargoWagon
            $table->foreignId('resource_id')->constrained()->onDelete('cascade'); // Зв'язок з Resource
            $table->integer('quantity')->default(0); // Кількість ресурсу у вагоні
            $table->timestamps();

            // Унікальний ключ для запобігання дублікатам ресурсів в одному вагоні
            $table->unique(['cargo_wagon_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_wagon_resources');
    }
};
