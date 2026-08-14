<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Minimal RFC 6455 text-frame codec, sufficient for the Discord gateway.
 */
final class WebSocketFrame
{
    private const FIN_TEXT = 0x81;

    private const MASK_BIT = 0x80;

    private const LENGTH_16_BIT = 126;

    private const LENGTH_64_BIT = 127;

    public static function encode(string $payload): string
    {
        $length = strlen($payload);
        $frame = pack('C', self::FIN_TEXT);

        if ($length <= 125) {
            $frame .= pack('C', $length | self::MASK_BIT);
        } elseif ($length <= 65535) {
            $frame .= pack('C', self::LENGTH_16_BIT | self::MASK_BIT).pack('n', $length);
        } else {
            $frame .= pack('C', self::LENGTH_64_BIT | self::MASK_BIT).pack('J', $length);
        }

        $mask = random_bytes(4);
        $frame .= $mask;

        for ($i = 0; $i < $length; $i++) {
            $frame .= $payload[$i] ^ $mask[$i % 4];
        }

        return $frame;
    }

    public static function decode(string $frame): ?string
    {
        if (strlen($frame) < 2) {
            return null;
        }

        $secondByte = ord($frame[1]);
        $isMasked = ($secondByte & self::MASK_BIT) === self::MASK_BIT;
        $payloadLength = $secondByte & 0x7F;

        $offset = match ($payloadLength) {
            self::LENGTH_16_BIT => 4,
            self::LENGTH_64_BIT => 10,
            default => 2,
        };

        if (! $isMasked) {
            return substr($frame, $offset);
        }

        $mask = substr($frame, $offset, 4);
        $offset += 4;
        $payload = '';

        for ($i = $offset, $length = strlen($frame); $i < $length; $i++) {
            $payload .= $frame[$i] ^ $mask[($i - $offset) % 4];
        }

        return $payload;
    }
}
