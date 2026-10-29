<?php

declare(strict_types=1);

use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

function fakeOAuthUser(string $provider, string $id, array $attributes = []): void
{
    $account = (new SocialiteUser)->map($attributes + [
        'id' => $id,
        'nickname' => 'user'.$id,
        'name' => 'User '.$id,
        'email' => $id.'@example.com',
        'avatar' => 'https://avatars.example.com/'.$id.'.png',
    ]);

    $driver = Mockery::mock(Provider::class);
    $driver->shouldReceive('user')->andReturn($account);
    Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
}
