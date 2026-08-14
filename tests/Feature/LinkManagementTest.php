<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Profile;

test('an authenticated user reaches the links page', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->get('/admin/links')->assertSuccessful();
});

test('a link is appended at the end of the list', function (): void {
    $profile = Profile::factory()->create();

    Link::create([
        'profile_id' => $profile->id,
        'title' => 'Existing',
        'url' => 'https://example.com',
        'icon' => 'globe',
        'sort_order' => 4,
    ]);

    $this->actingAs($profile->user)->post('/admin/links', [
        'title' => 'GitHub',
        'url' => 'https://github.com',
        'icon' => 'github',
    ])->assertRedirect();

    $this->assertDatabaseHas('links', [
        'title' => 'GitHub',
        'sort_order' => 5,
        'is_visible' => true,
    ]);
});

test('an email address is normalised into a mailto link', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->post('/admin/links', [
        'title' => 'Email',
        'url' => 'hello@example.com',
        'icon' => 'envelope',
    ])->assertRedirect();

    $this->assertDatabaseHas('links', ['url' => 'mailto:hello@example.com']);
});

test('reordering rewrites the sort order', function (): void {
    $profile = Profile::factory()->create();

    $first = Link::create(['profile_id' => $profile->id, 'title' => 'A', 'url' => 'https://a.test', 'icon' => 'globe', 'sort_order' => 1]);
    $second = Link::create(['profile_id' => $profile->id, 'title' => 'B', 'url' => 'https://b.test', 'icon' => 'globe', 'sort_order' => 2]);

    $this->actingAs($profile->user)
        ->postJson('/admin/links/reorder', ['order' => [$second->id, $first->id]])
        ->assertSuccessful();

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

test('clicking a link increments its counter', function (): void {
    $profile = Profile::factory()->create();

    $link = Link::create([
        'profile_id' => $profile->id,
        'title' => 'GitHub',
        'url' => 'https://github.com',
        'icon' => 'github',
    ]);

    $this->postJson("/links/{$link->id}/click")->assertSuccessful();

    expect($link->fresh()->clicks_count)->toBe(1);
});

test('a link can be deleted', function (): void {
    $profile = Profile::factory()->create();

    $link = Link::create([
        'profile_id' => $profile->id,
        'title' => 'GitHub',
        'url' => 'https://github.com',
        'icon' => 'github',
    ]);

    $this->actingAs($profile->user)->delete("/admin/links/{$link->id}")->assertRedirect();

    $this->assertDatabaseMissing('links', ['id' => $link->id]);
});
