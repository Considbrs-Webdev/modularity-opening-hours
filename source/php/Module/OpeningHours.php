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
        if (array_key_exists('weekNo', $raw)) {
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
     * @param array<int, array> $repeater
     * @param int $year
     * @return array<int, array{weekLabel: string, weekNo: int, days: array<int, array{name: string, slots: array}>}>
     */
    private function buildWeeksFromRepeater(array $repeater, int $year): array
    {
        $weeks = [];
        $dayNames = [
            'monday' => __('Monday', 'modularity-opening-hours'),
            'tuesday' => __('Tuesday', 'modularity-opening-hours'),
            'wednesday' => __('Wednesday', 'modularity-opening-hours'),
            'thursday' => __('Thursday', 'modularity-opening-hours'),
            'friday' => __('Friday', 'modularity-opening-hours'),
            'saturday' => __('Saturday', 'modularity-opening-hours'),
            'sunday' => __('Sunday', 'modularity-opening-hours'),
        ];

        foreach ($repeater as $row) {
            if (!is_array($row)) {
                continue;
            }

            $weekNo = (int) ($row['weekNo'] ?? 0);
            if ($weekNo < 1 || $weekNo > 53) {
                continue;
            }

            // Get repeat count (default 0 = just this week, no additional repeats)
            $repeatCount = (int) ($row['repeatThisPatternForXWeeks'] ?? 0);
            if ($repeatCount < 0) {
                $repeatCount = 0;
            }

            // Total weeks = 1 (the starting week) + repeatCount (additional weeks)
            $totalWeeks = 1 + $repeatCount;

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

            // Special opening hours overrides (per day)
            $specialHours = $row['specialOpeningHoursThisWeek'] ?? [];
            $specialHours = is_array($specialHours) ? $specialHours : [];

            // Build base days structure
            $baseDays = [
                'monday' => $monFriSlots,
                'tuesday' => $monFriSlots,
                'wednesday' => $monFriSlots,
                'thursday' => $monFriSlots,
                'friday' => $monFriSlots,
                'saturday' => $satSlots,
                'sunday' => $sunSlots,
            ];

            // Apply special hours overrides
            foreach ($specialHours as $special) {
                if (!is_array($special)) {
                    continue;
                }
                $dayKey = $special['dayOfTheWeek'] ?? null;
                if ($dayKey && array_key_exists($dayKey, $baseDays)) {
                    $specialOpen = $this->normalizeTime((string) ($special['opensThisDay'] ?? ''));
                    $specialClose = $this->normalizeTime((string) ($special['closesThisDay'] ?? ''));
                    if ($specialOpen !== '' && $specialClose !== '') {
                        $baseDays[$dayKey] = [['open' => $specialOpen, 'close' => $specialClose]];
                    } else {
                        $baseDays[$dayKey] = [['closed' => true]];
                    }
                }
            }

            // Generate weeks for the range
            for ($i = 0; $i < $totalWeeks; $i++) {
                $currentWeekNo = $weekNo + $i;
                if ($currentWeekNo > 53) {
                    break;
                }

                $weekOne = new \DateTimeImmutable("{$year}-01-04");
                $monday = $weekOne->modify('monday this week')->modify('+' . ($currentWeekNo - 1) . ' weeks');
                $weekEnd = $monday->modify('+6 days');
                $weekLabel = sprintf(
                    __('Week %1$s %2$s (%3$s – %4$s)', 'modularity-opening-hours'),
                    (string) $currentWeekNo,
                    (string) $year,
                    $monday->format('j M'),
                    $weekEnd->format('j M')
                );

                $weeks[] = [
                    'weekLabel' => $weekLabel,
                    'weekNo' => $currentWeekNo,
                    'days' => [
                        ['name' => $dayNames['monday'], 'slots' => $baseDays['monday']],
                        ['name' => $dayNames['tuesday'], 'slots' => $baseDays['tuesday']],
                        ['name' => $dayNames['wednesday'], 'slots' => $baseDays['wednesday']],
                        ['name' => $dayNames['thursday'], 'slots' => $baseDays['thursday']],
                        ['name' => $dayNames['friday'], 'slots' => $baseDays['friday']],
                        ['name' => $dayNames['saturday'], 'slots' => $baseDays['saturday']],
                        ['name' => $dayNames['sunday'], 'slots' => $baseDays['sunday']],
                    ],
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

        return array_reverse($uniqueWeeks);
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