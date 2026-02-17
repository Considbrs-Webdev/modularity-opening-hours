<?php

declare(strict_types=1);

namespace ModularityOpeningHours\Module;

/**
 * Class OpeningHours
 * @package ModularityOpeningHours\Module
 */
class OpeningHours extends \Modularity\Module
{
    public $slug = 'opening-hours';
    public $supports = [];

    public function init(): void
    {
        $this->nameSingular = __('OpeningHours', 'modularity-opening-hours');
        $this->namePlural = __('OpeningHours', 'modularity-opening-hours');
        $this->description = __('A opening-hours module.', 'modularity-opening-hours');
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array
    {
        $data = [];
        $data = array_merge($data, (array) \Modularity\Helper\FormatObject::camelCase(
            $this->getFields(),
        ));

        $weekRepeater = $this->normalizeWeekRepeater($data['weekRepeater'] ?? null);
        $year = $this->getYear();
        $weeks = $this->buildWeeksFromRepeater($weekRepeater, $year);
        $currentIndex = $this->getCurrentWeekIndex($weeks);
        $data['weeks'] = $weeks;
        $data['weeksJson'] = json_encode($weeks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
        $data['year'] = $year;
        $data['currentWeekIndex'] = $currentIndex;
        $data['currentWeek'] = $weeks[$currentIndex] ?? null;
        $data['totalWeeks'] = count($weeks);
        $data['hasPrev'] = $currentIndex > 0;
        $data['hasNext'] = $currentIndex < count($weeks) - 1;

        return $data;
    }

    /**
     * Ensures weekRepeater is always an array of rows.
     * @param mixed $raw
     * @return array<int, array>
     */
    private function normalizeWeekRepeater($raw): array
    {
        if (!is_array($raw) || empty($raw)) {
            return [];
        }
        if (isset($raw[0]) && is_array($raw[0])) {
            return array_values($raw);
        }
        if (array_key_exists('weekNoFrom', $raw) || array_key_exists('weekNoTo', $raw)) {
            return [$raw];
        }
        return [];
    }

    /**
     * @return int
     */
    private function getYear(): int
    {
        if (isset($_GET['year']) && is_numeric($_GET['year'])) {
            return (int) $_GET['year'];
        }
        return (int) (new \DateTimeImmutable())->format('Y');
    }

    /**
     * @param array<int, array{weekLabel: string, weekNo?: int, days: array}> $weeks
     * @return int
     */
    private function getCurrentWeekIndex(array $weeks): int
    {
        if (empty($weeks)) {
            return 0;
        }
        if (isset($_GET['week']) && is_numeric($_GET['week'])) {
            return max(0, min((int) $_GET['week'], count($weeks) - 1));
        }
        $isoWeek = (int) (new \DateTimeImmutable())->format('W');
        foreach ($weeks as $i => $week) {
            if (($week['weekNo'] ?? 0) === $isoWeek) {
                return $i;
            }
        }
        return 0;
    }

    /**
     * @param string $time ACF time (H:i:s or g:i a)
     * @return string H:i
     */
    private function normalizeTime(string $time): string
    {
        if ($time === '') {
            return '';
        }
        $dt = \DateTimeImmutable::createFromFormat('H:i:s', $time);
        if ($dt !== false) {
            return $dt->format('H:i');
        }
        $dt = \DateTimeImmutable::createFromFormat('g:i a', $time);
        if ($dt !== false) {
            return $dt->format('H:i');
        }
        return $time;
    }

    /**
     * Parse date string from ACF date picker (d/m/Y format)
     * @param string $dateStr
     * @return \DateTimeImmutable|null
     */
    private function parseDate(string $dateStr): ?\DateTimeImmutable
    {
        if ($dateStr === '') {
            return null;
        }
        $dt = \DateTimeImmutable::createFromFormat('d/m/Y', $dateStr);
        if ($dt !== false) {
            return $dt->setTime(0, 0, 0);
        }
        return null;
    }

    /**
     * Get localized month name (short)
     * @param int $month
     * @return string
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => __('Jan', 'modularity-opening-hours'),
            2 => __('Feb', 'modularity-opening-hours'),
            3 => __('Mar', 'modularity-opening-hours'),
            4 => __('Apr', 'modularity-opening-hours'),
            5 => __('May', 'modularity-opening-hours'),
            6 => __('Jun', 'modularity-opening-hours'),
            7 => __('Jul', 'modularity-opening-hours'),
            8 => __('Aug', 'modularity-opening-hours'),
            9 => __('Sep', 'modularity-opening-hours'),
            10 => __('Oct', 'modularity-opening-hours'),
            11 => __('Nov', 'modularity-opening-hours'),
            12 => __('Dec', 'modularity-opening-hours'),
        ];
        return $months[$month] ?? '';
    }

    /**
     * @param array<int, array> $repeater
     * @param int $year
     * @return array<int, array{weekLabel: string, weekNo: int, days: array<int, array{name: string, date: string, slots: array}>}>
     */
    private function buildWeeksFromRepeater(array $repeater, int $year): array
    {
        $weeks = [];
        $dayNames = [
            1 => __('Monday', 'modularity-opening-hours'),
            2 => __('Tuesday', 'modularity-opening-hours'),
            3 => __('Wednesday', 'modularity-opening-hours'),
            4 => __('Thursday', 'modularity-opening-hours'),
            5 => __('Friday', 'modularity-opening-hours'),
            6 => __('Saturday', 'modularity-opening-hours'),
            7 => __('Sunday', 'modularity-opening-hours'),
        ];

        // Collect all special opening hours by date from all repeater rows
        $allSpecialHours = [];

        foreach ($repeater as $row) {
            if (!is_array($row)) {
                continue;
            }

            $weekNoFrom = (int) ($row['weekNoFrom'] ?? 0);
            $weekNoTo = (int) ($row['weekNoTo'] ?? 0);

            // Validate week numbers
            if ($weekNoFrom < 1 || $weekNoFrom > 53) {
                continue;
            }
            if ($weekNoTo < 1 || $weekNoTo > 53) {
                $weekNoTo = $weekNoFrom;
            }
            if ($weekNoTo < $weekNoFrom) {
                $weekNoTo = $weekNoFrom;
            }

            // Base hours for Mon-Fri
            $monFriOpen = $this->normalizeTime((string) ($row['opens'] ?? ''));
            $monFriClose = $this->normalizeTime((string) ($row['closes'] ?? ''));
            $monFriSlots = ($monFriOpen !== '' && $monFriClose !== '')
                ? [['open' => $monFriOpen, 'close' => $monFriClose]]
                : [['closed' => true]];

            // Saturday
            $closedSat = !empty($row['closedSaturday']);
            $satOpen = $this->normalizeTime((string) ($row['opensSaturday'] ?? ''));
            $satClose = $this->normalizeTime((string) ($row['closesSaturday'] ?? ''));
            $satSlots = $closedSat || $satOpen === '' || $satClose === ''
                ? [['closed' => true]]
                : [['open' => $satOpen, 'close' => $satClose]];

            // Sunday
            $closedSun = !empty($row['closedSunday']);
            $sunOpen = $this->normalizeTime((string) ($row['opensSunday'] ?? ''));
            $sunClose = $this->normalizeTime((string) ($row['closesSunday'] ?? ''));
            $sunSlots = $closedSun || $sunOpen === '' || $sunClose === ''
                ? [['closed' => true]]
                : [['open' => $sunOpen, 'close' => $sunClose]];

            // Collect special opening hours (by date) from this row
            $specialHours = $row['specialOpeningHours'] ?? [];
            $specialHours = is_array($specialHours) ? $specialHours : [];
            
            foreach ($specialHours as $special) {
                if (!is_array($special)) {
                    continue;
                }
                $specialDate = $this->parseDate((string) ($special['specialOpeningDate'] ?? ''));
                if ($specialDate === null) {
                    continue;
                }
                $dateKey = $specialDate->format('Y-m-d');
                $specialOpen = $this->normalizeTime((string) ($special['opensThisDay'] ?? ''));
                $specialClose = $this->normalizeTime((string) ($special['closesThisDay'] ?? ''));
                
                if ($specialOpen !== '' && $specialClose !== '') {
                    $allSpecialHours[$dateKey] = [['open' => $specialOpen, 'close' => $specialClose]];
                } else {
                    $allSpecialHours[$dateKey] = [['closed' => true]];
                }
            }

            // Generate weeks for the range
            for ($currentWeekNo = $weekNoFrom; $currentWeekNo <= $weekNoTo; $currentWeekNo++) {
                $weekOne = new \DateTimeImmutable("{$year}-01-04");
                $monday = $weekOne->modify('monday this week')->modify('+' . ($currentWeekNo - 1) . ' weeks');
                $weekEnd = $monday->modify('+6 days');
                
                $weekLabel = sprintf(
                    __('Week %1$s %2$s (%3$s – %4$s)', 'modularity-opening-hours'),
                    (string) $currentWeekNo,
                    (string) $year,
                    $monday->format('j') . ' ' . $this->getMonthName((int) $monday->format('n')),
                    $weekEnd->format('j') . ' ' . $this->getMonthName((int) $weekEnd->format('n'))
                );

                // Build days with actual dates
                $days = [];
                for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                    $dayDate = $monday->modify("+{$dayOffset} days");
                    $dayOfWeek = (int) $dayDate->format('N');
                    $dateKey = $dayDate->format('Y-m-d');

                    // Determine base slots for this day of week
                    if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                        $baseSlots = $monFriSlots;
                    } elseif ($dayOfWeek === 6) {
                        $baseSlots = $satSlots;
                    } else {
                        $baseSlots = $sunSlots;
                    }

                    // Check for special hours override for this specific date
                    $slots = $allSpecialHours[$dateKey] ?? $baseSlots;

                    // Format date as "17 feb" (day + short month name)
                    $formattedDate = $dayDate->format('j') . ' ' . $this->getMonthName((int) $dayDate->format('n'));

                    $days[] = [
                        'name' => $dayNames[$dayOfWeek],
                        'date' => $formattedDate,
                        'dateKey' => $dateKey,
                        'slots' => $slots,
                    ];
                }

                $weeks[] = [
                    'weekLabel' => $weekLabel,
                    'weekNo' => $currentWeekNo,
                    'days' => $days,
                ];
            }
        }

        // Sort by week number
        usort($weeks, fn($a, $b) => $a['weekNo'] <=> $b['weekNo']);

        // Remove duplicate week numbers (later entries in original data win)
        $seen = [];
        $uniqueWeeks = [];
        foreach (array_reverse($weeks) as $week) {
            if (!isset($seen[$week['weekNo']])) {
                $seen[$week['weekNo']] = true;
                $uniqueWeeks[] = $week;
            }
        }

        $result = array_reverse($uniqueWeeks);

        // Apply special hours to all weeks (for dates that fall within their range)
        foreach ($result as &$week) {
            foreach ($week['days'] as &$day) {
                $dateKey = $day['dateKey'];
                if (isset($allSpecialHours[$dateKey])) {
                    $day['slots'] = $allSpecialHours[$dateKey];
                }
            }
        }

        return $result;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return 'opening-hours.blade.php';
    }

    /**
     * Style - Register & adding css
     * @return void
     */
    public function style(): void
    {
        $this->wpEnqueue?->add('css/modularity-opening-hours.css', [], '1.0.0');
    }

    /**
     * Script - Register & adding js
     * @return void
     */
    public function script(): void
    {
        $scriptFile = \ModularityOpeningHours\Helper\CacheBust::name('js/modularity-opening-hours.js');
        if ($scriptFile) {
            $this->wpEnqueue?->add($scriptFile, [], null, true);
        }
    }
}