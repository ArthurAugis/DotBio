<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an authenticated user reaches the seo page', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)
        ->get('/admin/seo')
        ->assertSuccessful()
        ->assertSee('SEO');
});

test('an authenticated user updates the seo metadata', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)
        ->post('/admin/seo', [
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom Meta Description',
            'meta_keywords' => 'test, seo, dotbio',
            'favicon_type' => 'avatar',
            'meta_robots' => 'index, follow',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'meta_title' => 'Custom Meta Title',
        'meta_description' => 'Custom Meta Description',
        'meta_keywords' => 'test, seo, dotbio',
        'favicon_type' => 'avatar',
    ]);
});

test('the social preview image is saved on the public profile even when another account is logged in', function (): void {
    Storage::fake('public');
    $profile = Profile::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->post('/admin/seo', [
            'favicon_type' => 'avatar',
            'meta_robots' => 'index, follow',
            'meta_image_file' => UploadedFile::fake()->image('preview.png', 1200, 630),
        ])
        ->assertSessionHasNoErrors();

    expect(Profile::count())->toBe(1)
        ->and($profile->fresh()->meta_image)->toContain('uploads/meta_images/');
});

test('an invalid robots directive is rejected', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)
        ->post('/admin/seo', [
            'favicon_type' => 'avatar',
            'meta_robots' => 'do-whatever',
        ])
        ->assertSessionHasErrors('meta_robots');
});

test('the public profile renders the customized seo tags and favicon', function (): void {
    $profile = Profile::factory()->create([
        'display_name' => 'John Doe',
        'avatar_url' => 'uploads/avatars/test.png',
        'meta_title' => 'My Awesome Portfolio',
        'meta_description' => 'Check out my links and social media profiles.',
        'meta_keywords' => 'portfolio, john, developer',
        'favicon_type' => 'avatar',
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('<title>My Awesome Portfolio</title>', false)
        ->assertSee('<meta name="description" content="Check out my links and social media profiles.">', false)
        ->assertSee('<meta name="keywords" content="portfolio, john, developer">', false)
        ->assertSee($profile->favicon_source, false);
});
