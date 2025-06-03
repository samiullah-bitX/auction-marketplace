<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

function log_debug($message) {
    if (WP_DEBUG === true) {
        error_log('[AuctionMarketplace] ' . print_r($message, true));
    }
}
