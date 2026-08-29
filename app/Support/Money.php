<?php

namespace App\Support;

/**
 * Decimal-safe money arithmetic for ETB (2 decimal places).
 *
 * All financial math runs on integer minor units (cents) — never on PHP
 * floats — so results are deterministic and reproducible. Strings come in,
 * exact 2-decimal strings come out.
 */
final class Money
{
    public const SCALE = 2;

    public const MINOR_PER_UNIT = 100;

    public static function toMinor(string $amount): int
    {
        $amount = trim($amount);
        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '-');

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, self::SCALE), self::SCALE, '0');

        $minor = ((int) $whole * self::MINOR_PER_UNIT) + (int) $fraction;

        return $negative ? -$minor : $minor;
    }

    public static function fromMinor(int $minor): string
    {
        $negative = $minor < 0;
        $minor = abs($minor);

        return ($negative ? '-' : '')
            .intdiv($minor, self::MINOR_PER_UNIT)
            .'.'
            .str_pad((string) ($minor % self::MINOR_PER_UNIT), 2, '0', STR_PAD_LEFT);
    }

    /** "7.50" (percent) -> 750 basis points */
    public static function percentToBasisPoints(string $rate): int
    {
        [$whole, $fraction] = array_pad(explode('.', trim($rate), 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    /** Deterministic percentage of a minor-unit amount, rounded half away from zero. */
    public static function percentage(int $minorAmount, int $basisPoints): int
    {
        $product = $minorAmount * $basisPoints;
        $sign = $product < 0 ? -1 : 1;

        return $sign * intdiv(abs($product) + 5000, 10000);
    }

    public static function multiply(string $amount, int $factor): string
    {
        return self::fromMinor(self::toMinor($amount) * $factor);
    }

    /** "3000" -> "3000.00" */
    public static function normalize(string $amount): string
    {
        return self::fromMinor(self::toMinor($amount));
    }

    public static function negate(string $amount): string
    {
        return self::fromMinor(-self::toMinor($amount));
    }

    /** Sum decimal strings exactly via minor units. */
    public static function sum(string ...$amounts): string
    {
        $total = 0;

        foreach ($amounts as $amount) {
            $total += self::toMinor($amount);
        }

        return self::fromMinor($total);
    }

    public static function compare(string $a, string $b): int
    {
        return self::toMinor($a) <=> self::toMinor($b);
    }

    public static function isPositive(string $amount): bool
    {
        return self::toMinor($amount) > 0;
    }
}
