<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('title')->nullable();
            $table->string('description', 320)->nullable();
            $table->string('primary_keyword')->nullable()->index();
            $table->string('canonical_override')->nullable();
            $table->string('robots', 32)->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('slug_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('sluggable');
            $table->string('old_slug');
            $table->timestamp('created_at')->nullable();
            $table->unique(['sluggable_type', 'old_slug']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_url')->nullable();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('source', 24)->default('manual');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('not_found_logs', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->unsignedBigInteger('hits')->default(0);
            $table->boolean('is_bot')->default(false);
            $table->string('last_referrer')->nullable();
            $table->string('last_user_agent')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('resolved_redirect_id')->nullable()->constrained('redirects')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->morphs('faqable');
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('not_found_logs');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('slug_histories');
        Schema::dropIfExists('seo_meta');
    }
};
