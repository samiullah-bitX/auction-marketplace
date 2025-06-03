<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Plugin_Init {

    private static $instance = null;

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(plugin_dir_path(__DIR__) . 'auction-marketplace.php', [$this, 'on_activation']);
        add_action('init', [$this, 'register_hooks']);
    }

    public function register_hooks() {
        // Placeholders for jobs and shortcode registrations
    }
}
