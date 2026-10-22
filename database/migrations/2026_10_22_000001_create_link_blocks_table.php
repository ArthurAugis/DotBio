<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('button');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('image_url')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();
        });

        Schema::table('profiles', function (Blueprint $table): void {
            $table->boolean('link_page_enabled')->default(false);
            $table->string('link_page_direction')->default('right');
            $table->string('link_page_button_text')->nullable();
            $table->string('link_page_title')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'link_page_enabled',
                'link_page_direction',
                'link_page_button_text',
                'link_page_title',
            ]);
        });

        Schema::dropIfExists('link_blocks');
    }
};
