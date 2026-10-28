<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('profiles')->orderBy('id')->first();

        $owner = DB::table('users')
            ->where(fn (Builder $query) => $query->whereNotNull('discord_id')->orWhereNotNull('password'))
            ->orderBy('id')
            ->first();

        if (! $page || ! $owner) {
            return;
        }

        DB::transaction(function () use ($page, $owner): void {
            DB::table('profiles')->where('id', $page->id)->update([
                'user_id' => $owner->id,
                'discord_id' => $page->discord_id ?? $owner->discord_id,
            ]);

            DB::table('profiles')->where('id', '!=', $page->id)->delete();
            DB::table('users')->where('id', '!=', $owner->id)->delete();
        });
    }

    public function down(): void {}
};
