<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('content_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 32);
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('route_key')->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('category')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['type', 'slug']);
            $table->unique(['type', 'route_key']);
            $table->index(['type', 'status', 'published_at']);
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->string('group');
            $table->text('value');
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('content_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('content_type', 32);
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('action', 24);
            $table->string('title');
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_activities');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('content_entries');
        Schema::dropIfExists('admins');
    }
};
