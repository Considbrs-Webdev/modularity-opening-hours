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

        // Append field config
        $data = array_merge($data, (array) \Modularity\Helper\FormatObject::camelCase(
            $this->getFields(),
        ));

        return $data;
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
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}

