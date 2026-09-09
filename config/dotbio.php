<?php

declare(strict_types=1);

return [
    'repository' => env('DOTBIO_UPDATE_REPOSITORY', 'ArthurAugis/DotBio'),
    'branch' => env('DOTBIO_UPDATE_BRANCH', 'main'),
    'remote' => env('DOTBIO_UPDATE_REMOTE', 'origin'),

    'update_check_ttl' => (int) env('DOTBIO_UPDATE_CHECK_TTL', 5400),

    'self_update_enabled' => (bool) env('DOTBIO_SELF_UPDATE_ENABLED', true),

    'php_binary' => env('DOTBIO_PHP_BINARY'),

    'run_discord_bot_from_scheduler' => (bool) env('DOTBIO_RUN_DISCORD_BOT', false),
];
