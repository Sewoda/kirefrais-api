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
        Schema::table('offer_subscriptions', function (Blueprint $table) {
            $table->integer('duration_weeks')->default(1)->after('meals_per_week');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('offer_subscriptions', function (Blueprint $table) {
            $table->dropColumn('duration_weeks');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
