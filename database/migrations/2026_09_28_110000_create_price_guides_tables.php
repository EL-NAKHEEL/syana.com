<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Set only when starting_price really changes: drives «آخر تحديث» and {year} in price titles.
            $table->timestamp('price_changed_at')->nullable()->after('price_note');
        });

        Schema::create('price_guides', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('h1');
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->string('scope', 20)->default('services');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('price_guide_service', function (Blueprint $table) {
            $table->foreignId('price_guide_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->primary(['price_guide_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_guide_service');
        Schema::dropIfExists('price_guides');
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('price_changed_at'));
    }
};
