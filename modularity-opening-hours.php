<?php

/**
 * Plugin Name:       Modularity OpeningHours
 * Description:       A opening-hours for creating Modularity modules.
 * Version: 1.0.0
 * Author:            Considbrs-Webdev
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-opening-hours
 * Domain Path:       /languages
 */

// Protect against direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYOPENINGHOURS_PATH', plugin_dir_path(__FILE__));
define('MODULARITYOPENINGHOURS_URL', plugins_url('', __FILE__));
define('MODULARITYOPENINGHOURS_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITYOPENINGHOURS_MODULE_PATH', MODULARITYOPENINGHOURS_PATH . 'source/php/Module/');

// Load text domain
add_action('init', function () {
    load_plugin_textdomain('modularity-opening-hours', false, plugin_basename(dirname(__FILE__)) . '/languages');
});

// Autoload from plugin
if (file_exists(MODULARITYOPENINGHOURS_PATH . 'vendor/autoload.php')) {
    require_once MODULARITYOPENINGHOURS_PATH . 'vendor/autoload.php';
}

// ACF auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-opening-hours');
    $acfExportManager->setExportFolder(MODULARITYOPENINGHOURS_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'opening-hours-module' => 'group_opening-hours_module',
    ));
    $acfExportManager->import();
});

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-opening-hours'] = MODULARITYOPENINGHOURS_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityOpeningHours\App();

