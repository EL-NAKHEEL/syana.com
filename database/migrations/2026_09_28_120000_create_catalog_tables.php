<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('model_number');
            $table->string('sku')->nullable()->unique();
            $table->string('type', 20)->index();
            $table->decimal('hp', 4, 2)->index();
            $table->unsignedInteger('btu')->nullable();
            $table->string('cooling', 10)->default('cool');
            $table->boolean('is_inverter')->default(false);
            $table->string('energy_class', 20)->nullable();
            $table->unsignedSmallInteger('room_area_min')->nullable();
            $table->unsignedSmallInteger('room_area_max')->nullable();
            $table->unsignedSmallInteger('warranty_months')->nullable();
            $table->string('warranty_note')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->string('installments_note')->nullable();
            $table->boolean('installation_included')->nullable();
            $table->string('stock_status', 20)->default('in_stock')->index();
            $table->json('specs')->nullable();
            $table->string('short_description', 300)->nullable();
            $table->longText('description')->nullable();
            $table->text('search_text')->nullable();
            $table->timestamp('price_changed_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_price_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2);
            $table->decimal('old_sale_price', 10, 2)->nullable();
            $table->decimal('new_sale_price', 10, 2)->nullable();
            $table->timestamp('changed_at');
        });

        Schema::create('related_products', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->primary(['product_id', 'related_id']);
        });

        Schema::create('facet_pages', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->foreignId('brand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('hp', 4, 2)->nullable();
            $table->string('type', 20)->nullable();
            $table->string('path')->unique();
            $table->string('h1')->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('area_text', 120)->nullable();
            $table->string('address', 500);
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('payment_method', 30);
            $table->string('payment_status', 20)->default('pending');
            $table->string('status', 20)->default('new');
            $table->json('utm')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('model_number');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedSmallInteger('qty');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['order_items', 'orders', 'facet_pages', 'related_products', 'product_price_changes', 'products', 'brands'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
