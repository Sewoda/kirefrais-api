<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_zone_id')->constrained()->onDelete('cascade');
            $table->foreignId('deliverer_id')->nullable()
                  ->references('id')->on('users')->onDelete('set null');

            $table->enum('status', [
                'pending',
                'paid',
                'preparing',
                'ready',
                'delivering',
                'delivered',
                'cancelled',
            ])->default('pending');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->enum('payment_method', ['flooz', 'tmoney', 'card', 'cash']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending');
            $table->string('payment_reference')->nullable();

            $table->date('delivery_date');
            $table->enum('delivery_slot', ['morning', 'afternoon', 'evening']);
            $table->timestamp('delivered_at')->nullable();

            $table->boolean('is_subscription')->default(false);
            $table->string('promo_code')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
