<?php

declare(strict_types=1);

namespace ModularityOpeningHours\Helper;

/**
 * Resolves which parts (week number, dates, year) make up the week heading of an
 * opening hours module, and formats the heading text.
 *
 * Modules saved before the `week_heading_parts` field existed only carry the legacy
 * `show_year` and `show_week_dates` values. For those, the field value is derived from the
 * legacy values so the heading renders exactly as before.
 *
 * @package ModularityOpeningHours\Helper
 */
class WeekHeading
{
    public const FIELD_NAME = 'week_heading_parts';
    public const PART_WEEK = 'week';
    public const PART_DATES = 'dates';
    public const PART_YEAR = 'year';

    /**
     * @return string[]
     */
    public static function allowedParts(): array
    {
        return [self::PART_WEEK, self::PART_DATES, self::PART_YEAR];
    }

    /**
     * Provides the heading parts for modules and blocks saved before the field existed.
     *
     * Hooked on `acf/pre_load_value`. Returns null whenever the field has a saved value
     * (including an empty selection), so ACF loads it as usual. Works for both posts and
     * blocks because it reads through ACF's metadata API.
     *
     * @param mixed $value Short-circuit value, null unless another filter set one.
     * @param int|string $postId ACF post ID, e.g. 123 or "block_abc".
     * @param array<string, mixed> $field ACF field definition.
     * @return mixed Legacy-derived parts, or the incoming value.
     */
    public static function preLoadParts($value, $postId, $field)
    {
        if ($value !== null || ($field['name'] ?? '') !== self::FIELD_NAME || !function_exists('acf_get_metadata')) {
            return $value;
        }

        if (acf_get_metadata($postId, self::FIELD_NAME) !== null) {
            return null;
        }

        return self::legacyParts($postId);
    }

    /**
     * Parts equivalent to the pre-`week_heading_parts` toggles. A missing toggle counts as on,
     * matching the old field default.
     *
     * @param int|string $postId ACF post ID.
     * @return string[]
     */
    public static function legacyParts($postId): array
    {
        $parts = [self::PART_WEEK];

        if (self::legacyToggleIsOn($postId, 'show_week_dates')) {
            $parts[] = self::PART_DATES;
        }

        if (self::legacyToggleIsOn($postId, 'show_year')) {
            $parts[] = self::PART_YEAR;
        }

        return $parts;
    }

    /**
     * Sanitize a field value to the allowed parts, in display order.
     *
     * `null` means the field was never saved and restores the default heading: week number,
     * dates and year. An empty checkbox comes back as `false`, `''`, or `[]` and hides every part.
     *
     * @param mixed $parts Value of the `week_heading_parts` field.
     * @return string[]
     */
    public static function normalizeParts($parts): array
    {
        if ($parts === false || $parts === '') {
            return [];
        }

        if ($parts === null) {
            return self::allowedParts();
        }

        if (!is_array($parts)) {
            return [];
        }

        return array_values(array_intersect(self::allowedParts(), array_map('strval', $parts)));
    }

    /**
     * Build the visible and the accessible week heading.
     *
     * The accessible label always contains week number, year and dates so assistive technology
     * announces the week even when the visible heading is shorter or hidden.
     *
     * @param string[] $parts Parts to show visibly.
     * @param int $weekNo ISO week number.
     * @param int $year Year.
     * @param string $startDate First day of the week, e.g. "19 okt".
     * @param string $endDate Last day of the week, e.g. "25 okt".
     * @return array{visible: string, full: string}
     */
    public static function buildLabels(array $parts, int $weekNo, int $year, string $startDate, string $endDate): array
    {
        return [
            'visible' => self::formatLabel($parts, $weekNo, $year, $startDate, $endDate),
            'full' => self::formatLabel(self::allowedParts(), $weekNo, $year, $startDate, $endDate),
        ];
    }

    /**
     * @param string[] $parts
     */
    private static function formatLabel(array $parts, int $weekNo, int $year, string $startDate, string $endDate): string
    {
        $showWeek = in_array(self::PART_WEEK, $parts, true);
        $showDates = in_array(self::PART_DATES, $parts, true);
        $showYear = in_array(self::PART_YEAR, $parts, true);

        if ($showWeek && $showDates && $showYear) {
            return sprintf(
                /* translators: 1: ISO week number, 2: year, 3: week start (day and month), 4: week end (day and month). */
                __('Week %1$s %2$s (%3$s – %4$s)', 'modularity-opening-hours'),
                (string) $weekNo,
                (string) $year,
                $startDate,
                $endDate
            );
        }

        if ($showWeek && $showYear) {
            return sprintf(
                /* translators: 1: ISO week number, 2: year. */
                __('Week %1$s %2$s', 'modularity-opening-hours'),
                (string) $weekNo,
                (string) $year
            );
        }

        if ($showWeek && $showDates) {
            return sprintf(
                /* translators: 1: ISO week number, 2: week start (day and month), 3: week end (day and month). */
                __('Week %1$s (%2$s – %3$s)', 'modularity-opening-hours'),
                (string) $weekNo,
                $startDate,
                $endDate
            );
        }

        if ($showWeek) {
            return sprintf(
                /* translators: 1: ISO week number. */
                __('Week %1$s', 'modularity-opening-hours'),
                (string) $weekNo
            );
        }

        if ($showDates && $showYear) {
            return sprintf('%1$s – %2$s %3$s', $startDate, $endDate, (string) $year);
        }

        if ($showDates) {
            return sprintf('%1$s – %2$s', $startDate, $endDate);
        }

        if ($showYear) {
            return (string) $year;
        }

        return '';
    }

    /**
     * @param int|string $postId
     */
    private static function legacyToggleIsOn($postId, string $name): bool
    {
        $stored = function_exists('acf_get_metadata') ? acf_get_metadata($postId, $name) : null;

        return $stored === null || !empty($stored);
    }
}
