<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();        // solo, duo, famille, grande-famille
            $table->string('name');                   // Offre Solo, Offre Duo, ...
            $table->integer('persons');               // 1, 2, 4, 6
            $table->string('icon')->nullable();       // icon path
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('offer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->string('name');                   // Abonnement 1, 2, 3
            $table->string('slug');                   // abonnement-1, abonnement-2, abonnement-3
            $table->integer('meals_per_week');         // 2, 4, 6
            $table->integer('price');                  // price in XOF
            $table->text('description')->nullable();
            $table->json('features')->nullable();      // list of feature strings
            $table->boolean('popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['offer_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_subscriptions');
        Schema::dropIfExists('offers');
    }
};
