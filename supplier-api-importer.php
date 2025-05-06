<?php
/**
 * Plugin Name: Select Piwah API WooCommerce Product Importer
 * Description: Piwah API WooCommerce Product Importer
 * Version: 1.1
 * Author: Tapiwah Siankuku
 * Author URI: https://tapiwah.co.za
 */


if (!defined('ABSPATH')) exit;

// Include plugin components
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/import-engine.php';
require_once plugin_dir_path(__FILE__) . 'includes/profile-manager.php';

// Hook into WP-Cron
add_action('supplier_api_cron_import', 'supplier_api_run_import');

// Schedule event on plugin activation
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('supplier_api_cron_import')) {
        wp_schedule_event(time(), 'daily', 'supplier_api_cron_import');
    }
});

// Clear scheduled event on plugin deactivation
register_deactivation_hook(__FILE__, function () {
    $timestamp = wp_next_scheduled('supplier_api_cron_import');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'supplier_api_cron_import');
    }
});
