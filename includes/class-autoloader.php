<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

spl_autoload_register(function ($class) {
    // Only autoload our plugin classes
    if (strpos($class, 'AuctionMarketplace\\') === false) {
        return;
    }

    $class_name = str_replace('AuctionMarketplace\\', '', $class);
    $file = plugin_dir_path(__DIR__) . 'includes/class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
