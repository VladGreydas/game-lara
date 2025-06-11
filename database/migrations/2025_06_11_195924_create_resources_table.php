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
            $table->string('name')->unique(); // Назва ресурсу (наприклад, 'Wood', 'Iron')
            $table->string('slug')->unique(); // Slug для програмної ідентифікації (наприклад, 'wood', 'iron')
            $table->text('description')->nullable(); // Опис ресурсу
            $table->string('unit')->default('unit'); // Одиниця виміру (наприклад, 'kg', 'item', 'm3') - за замовчуванням 'unit'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
