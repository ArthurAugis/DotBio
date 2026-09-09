<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class GatewayClosed extends RuntimeException
{
    public function __construct(public readonly ?int $closeCode)
    {
        parent::__construct($closeCode === null
            ? 'The Discord gateway closed the connection.'
            : sprintf('The Discord gateway closed the connection with code %d.', $closeCode));
    }

    public function isFatal(): bool
    {
        return in_array($this->closeCode, [4004, 4010, 4011, 4012, 4013, 4014], true);
    }

    public function explain(): string
    {
        return match ($this->closeCode) {
            4004 => 'The bot token is invalid. Set DISCORD_BOT_TOKEN to a fresh token.',
            4010 => 'The shard configuration sent to the gateway is invalid.',
            4011 => 'The bot is in too many guilds to run on a single shard.',
            4012 => 'The gateway version used by this build is no longer supported.',
            4013 => 'The requested intents are invalid.',
            4014 => 'The requested intents are not enabled for this application. Turn on the Presence and Server Members intents in the Discord Developer Portal.',
            default => 'The gateway will be reconnected.',
        };
    }
}
