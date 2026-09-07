<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('profiles', 'background_effect')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn('background_effect');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('profiles', 'background_effect')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('background_effect')->nullable();
        });
    }
};
