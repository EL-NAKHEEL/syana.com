<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->text('credentials')->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();
            $table->json('same_as')->nullable();
            $table->boolean('is_author')->default(true);
            $table->boolean('is_team_member')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('intro')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('excerpt', 300);
            $table->longText('body');
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('reading_minutes')->default(1);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('client_type', 20)->default('home');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('summary', 300);
            $table->longText('story')->nullable();
            $table->date('completed_on')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('reviewable');
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->unsignedTinyInteger('temp_before')->nullable();
            $table->unsignedTinyInteger('temp_after')->nullable();
            $table->string('video_url')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'projects', 'posts', 'post_categories', 'people'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
