<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('h1');
            $table->string('summary', 300);
            $table->text('intro')->nullable();
            $table->json('included')->nullable();
            $table->json('warning_signs')->nullable();
            $table->json('process_steps')->nullable();
            $table->json('price_factors')->nullable();
            $table->longText('body')->nullable();
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->string('price_note')->nullable();
            $table->string('schema_service_type')->nullable();
            $table->boolean('requires_24_7')->default(false);
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('governorate')->nullable();
            $table->text('local_intro')->nullable();
            $table->string('response_time_note')->nullable();
            $table->text('local_notes')->nullable();
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('area_service', function (Blueprint $table) {
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->primary(['area_id', 'service_id']);
        });

        Schema::create('area_neighbors', function (Blueprint $table) {
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('neighbor_id')->constrained('areas')->cascadeOnDelete();
            $table->primary(['area_id', 'neighbor_id']);
        });

        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('area_text', 120)->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ac_type', 40)->nullable();
            $table->date('preferred_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_url')->nullable();
            $table->json('utm')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
        Schema::dropIfExists('area_neighbors');
        Schema::dropIfExists('area_service');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('services');
    }
};
