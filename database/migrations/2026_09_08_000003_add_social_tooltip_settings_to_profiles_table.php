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
            $table->boolean('show_social_tooltips')->default(true);
            $table->string('social_tooltip_bg_color')->nullable();
            $table->float('social_tooltip_opacity')->default(1);
            $table->string('social_tooltip_text_color')->nullable();
            $table->string('social_tooltip_border_color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'show_social_tooltips',
                'social_tooltip_bg_color',
                'social_tooltip_opacity',
                'social_tooltip_text_color',
                'social_tooltip_border_color',
            ]);
        });
    }
};
