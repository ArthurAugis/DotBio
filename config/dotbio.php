<?php

declare(strict_types=1);

return [
    /*
     * Source repository the self-update feature pulls from. The update command
     * never takes a repository or branch from user input, only from here.
     */
    'repository' => env('DOTBIO_UPDATE_REPOSITORY', 'ArthurAugis/DotBio'),
    'branch' => env('DOTBIO_UPDATE_BRANCH', 'main'),
    'remote' => env('DOTBIO_UPDATE_REMOTE', 'origin'),

    /*
     * Kept slightly above the hourly scheduled check so the cached result never
     * expires between two runs and the admin badge stays accurate.
     */
    'update_check_ttl' => (int) env('DOTBIO_UPDATE_CHECK_TTL', 5400),

    'self_update_enabled' => (bool) env('DOTBIO_SELF_UPDATE_ENABLED', true),
];
