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
        $showYear = !empty($data['showYear']);
        $showWeekDates = !empty($data['showWeekDates']);
        $weeks = $this->buildWeeksFromRepeater($weekRepeater, $year, $showYear, $showWeekDates);
        $data['paginationBtnStyle'] = $data['paginationBtnStyle'] ?? 'text';
        $currentIndex = $this->getCurrentWeekIndex($weeks);
        $data['weeks'] = $weeks;
        $data['weeksJson'] = json_encode($weeks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
        $data['year'] = $year;
        $data['currentWeekIndex'] = $currentIndex;
        $data['currentWeek'] = $weeks[$currentIndex] ?? null;
        $data['totalWeeks'] = count($weeks);
        $data['hasPrev'] = $currentIndex > 0;
        $data['hasNext'] = $currentIndex < count($weeks) - 1;

        $highlightToday = !empty($data['highlightToday']);
        $showTomorrow = !empty($data['showTomorrowHighlight']);
        $todayDay = $highlightToday ? $this->getTodayDay($weeks) : null;
        $todayDateKey = (new \DateTimeImmutable())->format('Y-m-d');
        $tomorrowDateKey = (new \DateTimeImmutable($todayDateKey . ' +1 day'))->format('Y-m-d');
        $tomorrowDay = $showTomorrow ? $this->getDayByDateKeyFromWeeks($weeks, $tomorrowDateKey) : null;

        $data['showTodayHighlight'] = $highlightToday && !empty($todayDay);
        $data['todayDay'] = $todayDay;
        $data['showTomorrowHighlight'] = $showTomorrow;
        $data['tomorrowDay'] = $tomorrowDay;
        $data['todayDateKey'] = $todayDateKey;

        $styles = [];
        if (!empty($data['backgroundColor'])) {
            $styles[] = 'background-color: ' . $data['backgroundColor'];
        }
        if (!empty($data['textColor'])) {
            $styles[] = 'color: ' . $data['textColor'];
        }
        $closedColor = strtolower(trim($data['textColor'] ?? '')) === 'white' ? '#FCA5A5' : '#a11818';
        $styles[] = '--closed-color: ' . $closedColor;
        $data['inlineStyle'] = implode('; ', $styles);

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
            if ((int) ($week['weekNo'] ?? 0) === $isoWeek) {
                return $i;
            }
        }
        foreach ($weeks as $i => $week) {
            if ((int) ($week['weekNo'] ?? 0) >= $isoWeek) {
                return $i;
            }
        }
        return count($weeks) - 1;
    }

    /**
     * Find today's day entry from the weeks array by dateKey.
     * @param array<int, array{weekLabel: string, weekNo?: int, days: array}> $weeks
     * @return array{name: string, date: string, dateKey: string, slots: array}|null
     */
    private function getTodayDay(array $weeks): ?array
    {
        $todayKey = (new \DateTimeImmutable())->format('Y-m-d');
        foreach ($weeks as $week) {
            foreach ($week['days'] as $day) {
                if (($day['dateKey'] ?? '') === $todayKey) {
                    return $day;
                }
            }
        }
        return null;
    }

    /**
     * Find a day by dateKey in the weeks array.
     * @param array<int, array{weekLabel: string, weekNo?: int, days: array}> $weeks
     * @param string $dateKey
     * @return array{name: string, date: string, dateKey: string, slots: array}|null
     */
    private function getDayByDateKeyFromWeeks(array $weeks, string $dateKey): ?array
    {
        if ($dateKey === '') {
            return null;
        }
        foreach ($weeks as $week) {
            foreach ($week['days'] ?? [] as $day) {
                if (($day['dateKey'] ?? '') === $dateKey) {
                    return $day;
                }
            }
        }
        return null;
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
     * @param bool $showYear
     * @param bool $showWeekDates
     * @return array<int, array{weekLabel: string, weekNo: int, days: array<int, array{name: string, date: string, slots: array}>}>
     */
    private function buildWeeksFromRepeater(array $repeater, int $year, bool $showYear = true, bool $showWeekDates = true): array
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

            $mode = $row['scheduleMode'] ?? 'standard';
            if ($mode !== 'granular') {
                $mode = 'standard';
            }

            // Build day slots: granular = per-day, standard = Mon-Fri / Sat / Sun
            $daySlotsByDow = [];
            if ($mode === 'granular') {
                $granularMap = [
                    1 => ['opens' => 'mondayOpens', 'closes' => 'mondayCloses', 'closed' => 'mondayIsClosed'],
                    2 => ['opens' => 'tuesdayOpens', 'closes' => 'tuesdayCloses', 'closed' => 'tuesdayIsClosed'],
                    3 => ['opens' => 'wednesdayOpens', 'closes' => 'wednesdayCloses', 'closed' => 'wednesdayIsClosed'],
                    4 => ['opens' => 'thursdayOpens', 'closes' => 'thursdayCloses', 'closed' => 'thursdayIsClosed'],
                    5 => ['opens' => 'fridayOpens', 'closes' => 'fridayCloses', 'closed' => 'fridayIsClosed'],
                    6 => ['opens' => 'saturdayOpens', 'closes' => 'saturdayCloses', 'closed' => 'saturdayIsClosed'],
                    7 => ['opens' => 'sundayOpens', 'closes' => 'sundayCloses', 'closed' => 'sundayIsClosed'],
                ];
                for ($dow = 1; $dow <= 7; $dow++) {
                    $m = $granularMap[$dow];
                    $closed = !empty($row[$m['closed']]);
                    $open = $this->normalizeTime((string) ($row[$m['opens']] ?? ''));
                    $close = $this->normalizeTime((string) ($row[$m['closes']] ?? ''));
                    $daySlotsByDow[$dow] = ($closed || $open === '' || $close === '')
                        ? [['closed' => true]]
                        : [['open' => $open, 'close' => $close]];
                }
            } else {
                $monFriOpen = $this->normalizeTime((string) ($row['opens'] ?? ''));
                $monFriClose = $this->normalizeTime((string) ($row['closes'] ?? ''));
                $monFriSlots = ($monFriOpen !== '' && $monFriClose !== '')
                    ? [['open' => $monFriOpen, 'close' => $monFriClose]]
                    : [['closed' => true]];
                $closedSat = !empty($row['closedSaturday']);
                $satOpen = $this->normalizeTime((string) ($row['opensSaturday'] ?? ''));
                $satClose = $this->normalizeTime((string) ($row['closesSaturday'] ?? ''));
                $satSlots = $closedSat || $satOpen === '' || $satClose === ''
                    ? [['closed' => true]]
                    : [['open' => $satOpen, 'close' => $satClose]];
                $closedSun = !empty($row['closedSunday']);
                $sunOpen = $this->normalizeTime((string) ($row['opensSunday'] ?? ''));
                $sunClose = $this->normalizeTime((string) ($row['closesSunday'] ?? ''));
                $sunSlots = $closedSun || $sunOpen === '' || $sunClose === ''
                    ? [['closed' => true]]
                    : [['open' => $sunOpen, 'close' => $sunClose]];
                $daySlotsByDow = [
                    1 => $monFriSlots,
                    2 => $monFriSlots,
                    3 => $monFriSlots,
                    4 => $monFriSlots,
                    5 => $monFriSlots,
                    6 => $satSlots,
                    7 => $sunSlots,
                ];
            }

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
                $specialDescription = trim((string) ($special['specialOpeningHoursDescription'] ?? ''));
                
                if ($specialOpen !== '' && $specialClose !== '') {
                    $allSpecialHours[$dateKey] = [
                        'slots' => [['open' => $specialOpen, 'close' => $specialClose]],
                        'description' => $specialDescription,
                    ];
                } else {
                    $allSpecialHours[$dateKey] = [
                        'slots' => [['closed' => true]],
                        'description' => $specialDescription,
                    ];
                }
            }

            // Generate weeks for the range
            for ($currentWeekNo = $weekNoFrom; $currentWeekNo <= $weekNoTo; $currentWeekNo++) {
                $weekOne = new \DateTimeImmutable("{$year}-01-04");
                $monday = $weekOne->modify('monday this week')->modify('+' . ($currentWeekNo - 1) . ' weeks');
                $weekEnd = $monday->modify('+6 days');
                
                $weekLabel = $this->buildWeekLabel($currentWeekNo, $year, $monday, $weekEnd, $showYear, $showWeekDates);

                // Build days with actual dates
                $days = [];
                for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                    $dayDate = $monday->modify("+{$dayOffset} days");
                    $dayOfWeek = (int) $dayDate->format('N');
                    $dateKey = $dayDate->format('Y-m-d');

                    // Determine base slots for this day of week
                    $baseSlots = $daySlotsByDow[$dayOfWeek] ?? [['closed' => true]];

                    // Check for special hours override for this specific date
                    $specialEntry = $allSpecialHours[$dateKey] ?? null;
                    $slots = $specialEntry !== null ? $specialEntry['slots'] : $baseSlots;
                    $description = $specialEntry['description'] ?? '';

                    // Format date as "17 feb" (day + short month name)
                    $formattedDate = $dayDate->format('j') . ' ' . $this->getMonthName((int) $dayDate->format('n'));

                    $days[] = [
                        'name' => $dayNames[$dayOfWeek],
                        'date' => $formattedDate,
                        'dateKey' => $dateKey,
                        'slots' => $slots,
                        'description' => $description,
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
                    $day['slots'] = $allSpecialHours[$dateKey]['slots'];
                    $day['description'] = $allSpecialHours[$dateKey]['description'] ?? '';
                }
            }
        }

        return $result;
    }

    /**
     * Build week label based on display settings
     * @param int $weekNo
     * @param int $year
     * @param \DateTimeImmutable $monday
     * @param \DateTimeImmutable $weekEnd
     * @param bool $showYear
     * @param bool $showWeekDates
     * @return string
     */
    private function buildWeekLabel(int $weekNo, int $year, \DateTimeImmutable $monday, \DateTimeImmutable $weekEnd, bool $showYear, bool $showWeekDates): string
    {
        $startDate = $monday->format('j') . ' ' . $this->getMonthName((int) $monday->format('n'));
        $endDate = $weekEnd->format('j') . ' ' . $this->getMonthName((int) $weekEnd->format('n'));

        if ($showYear && $showWeekDates) {
            return sprintf(
                /* translators: 1: ISO week number, 2: year, 3: week start (day and month), 4: week end (day and month). */
                __('Week %1$s %2$s (%3$s – %4$s)', 'modularity-opening-hours'),
                (string) $weekNo,
                (string) $year,
                $startDate,
                $endDate
            );
        }

        if ($showYear && !$showWeekDates) {
            return sprintf(
                /* translators: 1: ISO week number, 2: year. */
                __('Week %1$s %2$s', 'modularity-opening-hours'),
                (string) $weekNo,
                (string) $year
            );
        }

        if (!$showYear && $showWeekDates) {
            return sprintf(
                /* translators: 1: ISO week number, 2: week start (day and month), 3: week end (day and month). */
                __('Week %1$s (%2$s – %3$s)', 'modularity-opening-hours'),
                (string) $weekNo,
                $startDate,
                $endDate
            );
        }

        // Neither year nor dates
        return sprintf(
            /* translators: 1: ISO week number. */
            __('Week %1$s', 'modularity-opening-hours'),
            (string) $weekNo
        );
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