<?php

declare(strict_types=1);

namespace Luremo\DataExportBuilder\helpers;

final class DateFilterHelper
{
    public static function normalizeDateInput(mixed $value): ?string
    {
        if (is_string($value)) {
            return self::normalizeDateString($value);
        }

        if (!is_array($value)) {
            return null;
        }

        $year = trim((string)($value['year'] ?? ''));
        $month = trim((string)($value['month'] ?? ''));
        $day = trim((string)($value['day'] ?? ''));

        if ($year !== '' && $month !== '' && $day !== '') {
            return sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
        }

        foreach (self::flattenScalarValues($value) as $candidate) {
            $normalized = self::normalizeDateString($candidate);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private static function normalizeDateString(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $value) === 1) {
            return substr($value, 0, 10);
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    /**
     * @return string[]
     */
    private static function flattenScalarValues(array $value): array
    {
        $results = [];

        array_walk_recursive($value, static function (mixed $item) use (&$results): void {
            if (is_scalar($item) || $item instanceof \Stringable) {
                $results[] = (string)$item;
            }
        });

        return $results;
    }
}
