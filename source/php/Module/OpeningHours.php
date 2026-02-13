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

        $weeks = $this->getOpeningWeeks();
        $currentIndex = $this->getCurrentWeekIndex($weeks);
        $data['weeks'] = $weeks;
        $data['currentWeekIndex'] = $currentIndex;
        $data['currentWeek'] = $weeks[$currentIndex] ?? null;
        $data['totalWeeks'] = count($weeks);
        $data['hasPrev'] = $currentIndex > 0;
        $data['hasNext'] = $currentIndex < count($weeks) - 1;

        return $data;
    }

    /**
     * @return int
     */
    private function getCurrentWeekIndex(array $weeks): int
    {
        if (empty($weeks)) {
            return 0;
        }
        $requested = isset($_GET['week']) ? (int) $_GET['week'] : 0;
        return max(0, min($requested, count($weeks) - 1));
    }

    /**
     * @return array<int, array{weekLabel: string, days: array<int, array{name: string, slots: array}>}>
     */
    private function getOpeningWeeks(): array
    {
        $weekSlots = [
            ['name' => __('Monday', 'modularity-opening-hours'), 'slots' => [['open' => '09:00', 'close' => '12:00'], ['open' => '13:00', 'close' => '17:00']]],
            ['name' => __('Tuesday', 'modularity-opening-hours'), 'slots' => [['open' => '09:00', 'close' => '17:00']]],
            ['name' => __('Wednesday', 'modularity-opening-hours'), 'slots' => [['open' => '09:00', 'close' => '12:00']]],
            ['name' => __('Thursday', 'modularity-opening-hours'), 'slots' => [['open' => '09:00', 'close' => '17:00']]],
            ['name' => __('Friday', 'modularity-opening-hours'), 'slots' => [['open' => '09:00', 'close' => '15:00']]],
            ['name' => __('Saturday', 'modularity-opening-hours'), 'slots' => [['closed' => true]]],
            ['name' => __('Sunday', 'modularity-opening-hours'), 'slots' => [['closed' => true]]],
        ];

        $weeks = [];
        $base = new \DateTimeImmutable('monday this week');
        for ($i = -1; $i <= 3; $i++) {
            $weekStart = $base->modify("{$i} week");
            $weekEnd = $weekStart->modify('+6 days');
            $weeks[] = [
                'weekLabel' => sprintf(
                    __('Week %1$s %2$s (%3$s – %4$s)', 'modularity-opening-hours'),
                    $weekStart->format('W'),
                    $weekStart->format('Y'),
                    $weekStart->format('j M'),
                    $weekEnd->format('j M')
                ),
                'days' => $weekSlots,
            ];
        }
        return $weeks;
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

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}

