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
        Schema::create('city_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade'); // Зв'язок з City
            $table->foreignId('resource_id')->constrained()->onDelete('cascade'); // Зв'язок з Resource
            $table->integer('quantity')->default(0); // Кількість ресурсу в місті
            $table->integer('base_quantity')->default(1000); // Додаємо базову кількість, яку місто намагається підтримувати
            $table->float('price_multiplier')->default(1.0); // Додаємо множник ціни, який буде впливати на динаміку цін
            $table->float('buy_price')->default(0); // Ціна купівлі за одиницю
            $table->float('sell_price')->default(0); // Ціна продажу за одиницю
            $table->timestamps();

            // Унікальний ключ для запобігання дублікатам ресурсів в одному місті
            $table->unique(['city_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_resources');
    }
};
