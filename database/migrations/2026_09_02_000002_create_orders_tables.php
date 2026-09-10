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
            $table->string('ref', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('nif', 32)->nullable();
            $table->string('address');
            $table->string('postal_code', 16);
            $table->string('city');
            $table->text('notes')->nullable();
            $table->string('shipping_method');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('payment_method');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 32)->default('AGUARDAR');
            $table->json('items_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name');
            $table->string('variation')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('line_total', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
