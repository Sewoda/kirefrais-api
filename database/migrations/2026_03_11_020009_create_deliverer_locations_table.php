<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverer_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();

            $table->index(['deliverer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverer_locations');
    }
};
