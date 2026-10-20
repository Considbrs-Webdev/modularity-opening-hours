<?php

namespace ModularityOpeningHours;

use ModularityOpeningHours\Helper\CacheBust;
use ModularityOpeningHours\Helper\WeekHeading;

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

        // Iframed block canvas. enqueue_block_editor_assets never reaches the preview.
        add_action('enqueue_block_assets', [$this, 'enqueueEditorAssets']);

        add_filter('acf/pre_load_value', [WeekHeading::class, 'preLoadParts'], 10, 3);
    }

    /**
     * Enqueue styles
     * 
     * @return void
     */
    public function enqueueStyles(): void
    {
        $url = $this->stylesheetUrl();

        if ($url !== '') {
            wp_enqueue_style('modularity-opening-hours', $url, [], null);
        }
    }

    /**
     * Enqueue the module stylesheet and paging script inside the block editor iframe.
     *
     * The block is server-rendered with every week in the markup, so the same
     * script that pages weeks on the front end gives editors a working preview.
     *
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        if (!is_admin()) {
            return;
        }

        $styleUrl = $this->stylesheetUrl();

        if ($styleUrl !== '') {
            wp_enqueue_style('modularity-opening-hours', $styleUrl, [], null);
        }

        $scriptFile = CacheBust::name('js/modularity-opening-hours.js');

        if ($scriptFile) {
            wp_enqueue_script(
                'modularity-opening-hours',
                MODULARITYOPENINGHOURS_URL . '/assets/dist/' . $scriptFile,
                [],
                null,
                true
            );
        }
    }

    /**
     * Built stylesheet URL, or an empty string when the Vite manifest has no entry.
     *
     * @return string
     */
    private function stylesheetUrl(): string
    {
        $styleFile = CacheBust::name('css/modularity-opening-hours.css');

        if (!$styleFile) {
            return '';
        }

        return MODULARITYOPENINGHOURS_URL . '/assets/dist/' . $styleFile;
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
