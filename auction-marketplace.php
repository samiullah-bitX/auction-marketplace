<?php
/**
 * Plugin Name: Auction Marketplace
 * Plugin URI: 
 * Description: Prevent direct access to this fileDescription: A custom WordPress plugin that fetches and displays car auction data from USA and Canada via API integration. The plugin utilizes CRON jobs to ensure auction listings are always accurate and up-to-date. It provides dedicated listing and detail pages for showcasing auction cars.
 * Version: 1.0.0
 * Author: Bitcraftx
 * Author URI: https://bitcraftx.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simeon-auction
 */

if (!defined('ABSPATH')) {
    exit;
}

use AuctionMarketplace\Plugin_Init;
use AuctionMarketplace\DB_Schema;
use AuctionMarketplace\Sync_Job;

// Define plugin constants
define('AUCTION_API_TOKEN', "96ded80c2a9dfb9fe007f4d77526f5b891609497aef159ac3ff0f8a9aa28ba2f");
// Load autoloader
require_once plugin_dir_path(__FILE__) . 'includes/class-autoloader.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';

// Enqueue scripts and styles
function vehicle_auction_enqueue_assets() {
    wp_enqueue_style(
        'vehicle-select2-style',
        plugins_url('assets/css/select2.min.css', __FILE__),
        array(),
        '1.0.0'
    );


    wp_enqueue_style(
        'vehicle-auction-style',
        plugins_url('assets/css/style.css', __FILE__),
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'vehicle-select2-script',
        plugins_url('assets/js/select2.min.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );
	
	wp_enqueue_script(
        'vehicle-libphonenumber-script',
        plugins_url('assets/js/libphonenumber-js.min.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'vehicle-auction-script',
        plugins_url('assets/js/script.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('vehicle-auction-script', 'carAuctionAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vehicle-auction-nonce')
    ));
}

add_action('wp_enqueue_scripts', 'vehicle_auction_enqueue_assets');

// Register activation hook BEFORE plugin is loaded
register_activation_hook(__FILE__, function () {
    require_once plugin_dir_path(__FILE__) . 'includes/class-db-schema.php';
    DB_Schema::create_tables();
});

// Deactivation hook to clear CRON jobs
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('auction_cron_event');
});

function auction_marketplace_run() {
    Plugin_Init::get_instance();
}

add_action('plugins_loaded', 'auction_marketplace_run');

function auction_marketplace_run_sync() {
    if (isset($_GET['run_auction_sync']) && $_GET['run_auction_sync'] == 1 && !defined('AUCTION_SYNC_RUNNING')) {
        $job = new Sync_Job();
        $job->run();
        exit('Sync complete!');
    }
}

add_action('admin_init', 'auction_marketplace_run_sync');


