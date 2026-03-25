<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_kits', function (Blueprint $table) {
            $table->decimal('price_1p', 10, 2)->nullable()->change();
            $table->decimal('price_2p', 10, 2)->nullable()->change();
            $table->decimal('price_4p', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('meal_kits', function (Blueprint $table) {
            $table->decimal('price_1p', 10, 2)->nullable(false)->change();
            $table->decimal('price_2p', 10, 2)->nullable(false)->change();
            $table->decimal('price_4p', 10, 2)->nullable(false)->change();
        });
    }
};
