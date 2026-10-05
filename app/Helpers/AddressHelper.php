<?php

declare(strict_types=1);

namespace App\Helpers;

class AddressHelper
{
    public static function decode($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    public static function normalize($raw, array $columnFallbacks = []): array
    {
        $address = self::decode($raw);

        $street = self::firstFilled([
            $address['street'] ?? null,
            $address['street_address'] ?? null,
            $address['address_line1'] ?? null,
            $address['line1'] ?? null,
            $address['address'] ?? null,
        ]);

        return [
            'country' => self::stringValue($columnFallbacks['country'] ?? ($address['country'] ?? '')),
            'state' => self::stringValue($columnFallbacks['state'] ?? ($address['state'] ?? '')),
            'city' => self::stringValue($columnFallbacks['city'] ?? ($address['city'] ?? '')),
            'postal_code' => self::stringValue($columnFallbacks['postal_code'] ?? ($address['postal_code'] ?? '')),
            'street' => $street,
        ];
    }

    public static function forStorage($raw, array $columnFallbacks = []): array
    {
        return self::normalize($raw, $columnFallbacks);
    }

    private static function firstFilled(array $values): string
    {
        foreach ($values as $value) {
            $value = self::stringValue($value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function stringValue($value): string
    {
        return is_string($value) ? trim($value) : (is_scalar($value) ? trim((string)$value) : '');
    }
}
