<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('audio_border_opacity')->nullable()->default(100);
            $table->unsignedTinyInteger('audio_radius')->nullable()->default(16);
            $table->unsignedTinyInteger('audio_blur')->nullable()->default(16);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn(['audio_border_opacity', 'audio_radius', 'audio_blur']);
        });
    }
};
