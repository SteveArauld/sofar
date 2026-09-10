<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('billing_same')->default(true)->after('city');
            $table->string('billing_name')->nullable()->after('billing_same');
            $table->string('billing_address')->nullable()->after('billing_name');
            $table->string('billing_postal_code', 16)->nullable()->after('billing_address');
            $table->string('billing_city')->nullable()->after('billing_postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['billing_same', 'billing_name', 'billing_address', 'billing_postal_code', 'billing_city']);
        });
    }
};
