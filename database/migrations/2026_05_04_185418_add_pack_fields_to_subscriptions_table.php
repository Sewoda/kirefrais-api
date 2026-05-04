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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_subscription_id')->nullable()->after('meal_kit_id');
            $table->integer('meals_per_week')->nullable()->after('offer_subscription_id');
            
            $table->foreign('offer_subscription_id')->references('id')->on('offer_subscriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['offer_subscription_id']);
            $table->dropColumn(['offer_subscription_id', 'meals_per_week']);
        });
    }
};
