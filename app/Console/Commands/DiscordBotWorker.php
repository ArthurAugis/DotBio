<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Profile;
use App\Services\DiscordApi;
use App\Support\WebSocketFrame;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class DiscordBotWorker extends Command
{
    private const GATEWAY_HOST = 'gateway.discord.gg';

    private const GATEWAY_URL = 'ssl://'.self::GATEWAY_HOST.':443';

    private const ALL_INTENTS = 32767;

    private const OP_HEARTBEAT = 1;

    private const OP_IDENTIFY = 2;

    private const OP_REQUEST_GUILD_MEMBERS = 8;

    private const OP_HELLO = 10;

    private const DEFAULT_HEARTBEAT_MS = 41250;

    private const RECONNECT_DELAY_SECONDS = 5;

    protected $signature = 'discord:bot';

    protected $description = 'Run the Discord gateway daemon that mirrors presence data into profiles';

    private ?DiscordApi $api = null;

    public function handle(): int
    {
        $token = config('services.discord.bot_token');

        if (! $token) {
            $this->error('DISCORD_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        $this->api = new DiscordApi($token);
        $this->info('Connecting to the Discord gateway...');

        while (true) {
            try {
                $this->listen($token);
            } catch (Throwable $exception) {
                $this->error(sprintf(
                    'Gateway connection lost (%s). Reconnecting in %d seconds.',
                    $exception->getMessage(),
                    self::RECONNECT_DELAY_SECONDS,
                ));
            }

            sleep(self::RECONNECT_DELAY_SECONDS);
        }
    }

    private function listen(string $token): void
    {
        $socket = $this->openSocket();

        try {
            $this->send($socket, [
                'op' => self::OP_IDENTIFY,
                'd' => [
                    'token' => $token,
                    'intents' => self::ALL_INTENTS,
                    'properties' => ['os' => PHP_OS_FAMILY, 'browser' => 'dotbio', 'device' => 'dotbio'],
                    'presence' => [
                        'status' => 'online',
                        'activities' => [['name' => 'DotBio', 'type' => 0]],
                        'afk' => false,
                    ],
                ],
            ]);

            $this->consume($socket);
        } finally {
            fclose($socket);
        }
    }

    /**
     * @return resource
     */
    private function openSocket()
    {
        $context = stream_context_create([
            'ssl' => ['verify_peer' => ! app()->isLocal(), 'verify_peer_name' => ! app()->isLocal()],
        ]);

        $socket = @stream_socket_client(self::GATEWAY_URL, $errorCode, $errorMessage, 10, STREAM_CLIENT_CONNECT, $context);

        if ($socket === false) {
            throw new RuntimeException("Unable to reach the Discord gateway: {$errorMessage} ({$errorCode})");
        }

        $key = base64_encode(random_bytes(16));

        fwrite($socket, implode("\r\n", [
            'GET /?v=10&encoding=json HTTP/1.1',
            'Host: '.self::GATEWAY_HOST,
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Key: '.$key,
            'Sec-WebSocket-Version: 13',
            '', '',
        ]));

        $handshake = '';

        while ($line = fgets($socket)) {
            $handshake .= $line;

            if (trim($line) === '') {
                break;
            }
        }

        if (! str_contains($handshake, '101 Switching Protocols')) {
            fclose($socket);

            throw new RuntimeException('WebSocket handshake rejected by the Discord gateway.');
        }

        $this->info('Gateway handshake accepted, bot is online.');

        return $socket;
    }

    /**
     * @param  resource  $socket
     */
    private function consume($socket): void
    {
        $lastHeartbeat = time();
        $heartbeatInterval = (int) (self::DEFAULT_HEARTBEAT_MS / 1000);
        $lastSequence = null;

        while (! feof($socket)) {
            stream_set_timeout($socket, 5);
            $raw = fread($socket, 8192);

            if ($raw !== false && $raw !== '') {
                $payload = json_decode((string) WebSocketFrame::decode($raw), true);

                if (is_array($payload)) {
                    $lastSequence = $payload['s'] ?? $lastSequence;

                    if (($payload['op'] ?? null) === self::OP_HELLO) {
                        $milliseconds = (int) ($payload['d']['heartbeat_interval'] ?? self::DEFAULT_HEARTBEAT_MS);
                        $heartbeatInterval = max(5, intdiv($milliseconds, 1000));
                    }

                    if (($payload['op'] ?? null) === self::OP_HEARTBEAT) {
                        $this->sendHeartbeat($socket, $lastSequence);
                        $lastHeartbeat = time();
                    }

                    $this->dispatch($socket, $payload);
                }
            }

            if (time() - $lastHeartbeat >= $heartbeatInterval) {
                $this->sendHeartbeat($socket, $lastSequence);
                $lastHeartbeat = time();
            }

            usleep(200_000);
        }
    }

    /**
     * @param  resource  $socket
     * @param  array<string, mixed>  $payload
     */
    private function dispatch($socket, array $payload): void
    {
        $event = $payload['t'] ?? null;
        $data = $payload['d'] ?? [];

        if ($event === 'PRESENCE_UPDATE') {
            $this->syncPresence($data);

            return;
        }

        if ($event === 'GUILD_CREATE') {
            if ($guildId = $data['id'] ?? null) {
                $this->send($socket, [
                    'op' => self::OP_REQUEST_GUILD_MEMBERS,
                    'd' => ['guild_id' => $guildId, 'query' => '', 'limit' => 0, 'presences' => true],
                ]);
            }
        }

        if (in_array($event, ['GUILD_CREATE', 'GUILD_MEMBERS_CHUNK'], true)) {
            foreach ($data['presences'] ?? [] as $presence) {
                $this->syncPresence($presence);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $presence
     */
    private function syncPresence(array $presence): void
    {
        $discordId = data_get($presence, 'user.id');

        if (! $discordId) {
            return;
        }

        $profile = Profile::where('discord_id', $discordId)->first();

        if (! $profile) {
            return;
        }

        $status = $this->resolveStatus($presence);

        $attributes = [
            'custom_discord_status' => $status,
            'custom_status_text' => $this->resolveActivity($presence),
        ];

        if ($asset = data_get($presence, 'user.avatar_decoration_data.asset')) {
            $attributes['discord_avatar_decoration_url'] = DiscordApi::decorationUrl((string) $asset);
        }

        if ($tag = data_get($presence, 'user.clan.tag')) {
            $attributes['discord_tag'] = (string) $tag;
        }

        if (! isset($attributes['discord_avatar_decoration_url'], $attributes['discord_tag'])) {
            $attributes = array_merge($attributes, $this->api?->profileAssets((string) $discordId) ?? []);
        }

        if ($status === 'offline' && $profile->custom_discord_status !== 'offline') {
            $attributes['last_seen_at'] = now('UTC');
        }

        $profile->update($attributes);

        $this->line("Presence synced for {$discordId}: {$status}");
    }

    /**
     * The gateway reports a global status plus a per-client breakdown; the first
     * non-offline client is the one actually reflecting the user's activity.
     *
     * @param  array<string, mixed>  $presence
     */
    private function resolveStatus(array $presence): string
    {
        foreach (['desktop', 'web', 'mobile'] as $client) {
            $status = data_get($presence, "client_status.{$client}");

            if (is_string($status) && $status !== 'offline') {
                return $status;
            }
        }

        return (string) ($presence['status'] ?? 'offline');
    }

    /**
     * @param  array<string, mixed>  $presence
     */
    private function resolveActivity(array $presence): string
    {
        $activities = collect($presence['activities'] ?? []);

        if ($state = data_get($activities->firstWhere('type', 4), 'state')) {
            return (string) $state;
        }

        if ($game = data_get($activities->firstWhere('type', 0), 'name')) {
            return 'Playing '.$game;
        }

        if ($song = data_get($presence, 'spotify.song')) {
            return 'Listening to '.$song;
        }

        return '';
    }

    /**
     * @param  resource  $socket
     * @param  array<string, mixed>  $payload
     */
    private function send($socket, array $payload): void
    {
        fwrite($socket, WebSocketFrame::encode((string) json_encode($payload)));
    }

    /**
     * @param  resource  $socket
     */
    private function sendHeartbeat($socket, ?int $sequence): void
    {
        $this->send($socket, ['op' => self::OP_HEARTBEAT, 'd' => $sequence]);
    }
}
