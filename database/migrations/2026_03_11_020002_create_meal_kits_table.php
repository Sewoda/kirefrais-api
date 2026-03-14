<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description');
            $table->text('ingredients');
            $table->json('images');
            $table->integer('prep_time');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');

            $table->integer('calories')->default(0);
            $table->decimal('proteins', 5, 1)->default(0);
            $table->decimal('carbs', 5, 1)->default(0);
            $table->decimal('fats', 5, 1)->default(0);
            $table->decimal('fiber', 5, 1)->default(0);

            $table->decimal('price_1p', 10, 2);
            $table->decimal('price_2p', 10, 2);
            $table->decimal('price_4p', 10, 2);

            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('is_active')->default(true);

            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('order_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_kits');
    }
};
