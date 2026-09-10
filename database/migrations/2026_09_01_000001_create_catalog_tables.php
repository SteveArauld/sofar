<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schéma du catalogue importé depuis dfpinteriores.com (scraper Python -> output/*.json).
 * Les clés primaires `id` reprennent les identifiants du site source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();      // id du site (peut être null au scrap -> voir importer)
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('parent_slug')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('url')->nullable();
            $table->string('catalog_url')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();      // id du site
            $table->string('erp_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('ean')->nullable();
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('category_name')->nullable();
            $table->string('category_slug')->nullable()->index();

            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_before', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->unsignedTinyInteger('vat_percent')->nullable();
            $table->string('availability')->nullable();
            $table->boolean('in_stock')->default(false);
            $table->string('stock')->nullable();
            $table->integer('stock_global')->nullable();
            $table->string('delivery_time_stock')->nullable();
            $table->string('delivery_time_order')->nullable();
            $table->integer('manufacturing_days')->nullable();

            $table->longText('short_description_html')->nullable();
            $table->longText('long_description_html')->nullable();
            $table->longText('logistic_data_html')->nullable();
            $table->longText('delivery_assembly_html')->nullable();

            $table->json('dimensions')->nullable();
            $table->json('variations')->nullable();
            $table->json('flags')->nullable();
            $table->json('breadcrumb')->nullable();
            $table->json('category_path')->nullable();

            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('views_20d')->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->unsignedInteger('images_count')->default(0);

            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('filename');
            $table->string('path');            // chemin réel relatif à public/ ex: assets/images/sofas/adriano-.../foto1.jpg
            $table->string('source_url')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('attr');
            $table->text('value')->nullable();
            $table->unsignedInteger('position')->default(0);

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_extra_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('name');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('variation')->nullable();
            $table->string('protec_id')->nullable();
            $table->string('extra_erpid')->nullable();
            $table->string('protec_erpid')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('author')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->text('body')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('raw')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('depth')->default(0);   // position dans le fil d'Ariane
            $table->primary(['category_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_extra_services');
        Schema::dropIfExists('product_specs');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
