<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('role', ['client', 'livreur', 'admin'])->default('client')->after('avatar');
            $table->string('google_id')->nullable()->after('role');
            $table->string('facebook_id')->nullable()->after('google_id');
            $table->boolean('is_active')->default(true)->after('facebook_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone','avatar','role','google_id','facebook_id','is_active']);
        });
    }
};
