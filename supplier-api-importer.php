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


// GitHub Plugin Updater
add_filter('site_transient_update_plugins', function ($transient) {
    if (empty($transient->checked)) return $transient;

    $plugin_slug = plugin_basename(__FILE__);
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];

    $remote = wp_remote_get('https://api.github.com/repos/
kingpiwah93/piwah-api-woocommerce-importer/releases/latest');

    if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200) {
        $release = json_decode(wp_remote_retrieve_body($remote));
        if (version_compare($current_version, $release->tag_name, '<')) {
            $transient->response[$plugin_slug] = (object) [
                'slug'        => $plugin_slug,
                'plugin'      => $plugin_slug,
                'new_version' => $release->tag_name,
                'url'         => $release->html_url,
                'package'     => $release->assets[0]->browser_download_url
            ];
        }
    }

    return $transient;
});
