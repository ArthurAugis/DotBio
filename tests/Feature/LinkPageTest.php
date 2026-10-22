<?php

declare(strict_types=1);

use App\Models\LinkBlock;
use App\Models\Profile;

test('an authenticated user reaches the link page manager', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->get('/admin/link-page')->assertSuccessful();
});

test('the link page settings are saved', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->post('/admin/link-page/settings', [
        'link_page_enabled' => 1,
        'link_page_direction' => 'bottom',
        'link_page_button_text' => 'My links',
    ])->assertRedirect();

    expect($profile->fresh())
        ->link_page_enabled->toBeTrue()
        ->link_page_direction->toBe('bottom')
        ->link_page_button_text->toBe('My links');
});

test('the link page style is saved and rendered as css variables', function (): void {
    $profile = Profile::factory()->create(['link_page_enabled' => true]);
    $style = Profile::LINK_PAGE_STYLE_DEFAULTS;
    $style['button']['radius'] = 8;
    $style['card']['bg_color'] = '#123456';

    $this->actingAs($profile->user)->post('/admin/link-page/settings', [
        'link_page_enabled' => 1,
        'link_page_direction' => 'right',
        'link_page_style' => $style,
    ])->assertSessionHasNoErrors();

    expect($profile->fresh()->link_page_styles['button']['radius'])->toEqual(8);

    auth()->logout();
    $this->get('/')->assertSee('--lp-radius: 8px;', false)->assertSee('--lp-bg: #123456;', false);
});

test('the link page style rejects values that could break the css', function (): void {
    $profile = Profile::factory()->create();
    $style = Profile::LINK_PAGE_STYLE_DEFAULTS;
    $style['button']['bg_color'] = 'red;} body{display:none';

    $this->actingAs($profile->user)->post('/admin/link-page/settings', [
        'link_page_enabled' => 1,
        'link_page_direction' => 'right',
        'link_page_style' => $style,
    ])->assertSessionHasErrors('link_page_style.button.bg_color');
});

test('a block is appended at the end of the page', function (): void {
    $profile = Profile::factory()->create();
    LinkBlock::create(['profile_id' => $profile->id, 'type' => 'heading', 'title' => 'Music', 'sort_order' => 3]);

    $this->actingAs($profile->user)->post('/admin/link-page/blocks', [
        'type' => 'card',
        'title' => 'New album',
        'subtitle' => 'Out now',
        'url' => 'https://example.com/album',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('link_blocks', ['title' => 'New album', 'sort_order' => 4]);
});

test('a block rejects scriptable urls', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->post('/admin/link-page/blocks', [
        'type' => 'button',
        'title' => 'Evil',
        'url' => 'javascript:alert(1)',
    ])->assertSessionHasErrors('url');
});

test('media urls resolve to embeddable players', function (string $url, ?string $src): void {
    $embed = (new LinkBlock(['media_url' => $url]))->embed;

    expect($embed['src'] ?? null)->toBe($src);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://youtu.be/dQw4w9WgXcQ?t=10', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
    ['https://open.spotify.com/intl-fr/track/4uLU6hMCjMI75M1A2tKUQC', 'https://open.spotify.com/embed/track/4uLU6hMCjMI75M1A2tKUQC'],
    ['/storage/uploads/link_videos/clip.mp4', '/storage/uploads/link_videos/clip.mp4'],
    ['https://example.com/page', null],
]);

test('the public profile renders visible blocks only', function (): void {
    $profile = Profile::factory()->create(['link_page_enabled' => true]);
    LinkBlock::create(['profile_id' => $profile->id, 'type' => 'button', 'title' => 'Shown block', 'url' => 'https://a.test']);
    LinkBlock::create(['profile_id' => $profile->id, 'type' => 'button', 'title' => 'Hidden block', 'is_visible' => false]);

    $this->get('/')->assertSuccessful()->assertSee('Shown block')->assertDontSee('Hidden block');
});

test('clicking a block increments its counter', function (): void {
    $profile = Profile::factory()->create();
    $block = LinkBlock::create(['profile_id' => $profile->id, 'type' => 'button', 'title' => 'Shop', 'url' => 'https://a.test']);

    $this->postJson("/blocks/{$block->id}/click")->assertSuccessful();

    expect($block->fresh()->clicks_count)->toBe(1);
});
