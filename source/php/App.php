<?php

namespace ModularityOpeningHours;

use ModularityOpeningHours\Helper\CacheBust;

/**
 * Class App
 * 
 * Main application bootstrap class.
 * Initialize your plugin components here.
 * 
 * @package ModularityOpeningHours
 */
class App
{
    public function __construct()
    {
        // Register module with Modularity
        add_action('init', [$this, 'registerModule']);

        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
    }

    /**
     * Enqueue styles
     * 
     * @return void
     */
    public function enqueueStyles(): void
    {
        $styleFile = CacheBust::name('css/modularity-opening-hours.css');

        if ($styleFile) {
            wp_enqueue_style(
                'modularity-opening-hours',
                MODULARITYOPENINGHOURS_URL . '/assets/dist/' . $styleFile,
                [],
                null
            );
        }
    }

    /**
     * Register the module with Modularity
     * 
     * @return void
     */
    public function registerModule(): void
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYOPENINGHOURS_MODULE_PATH,
                'OpeningHours',
            );
        }
    }
}
