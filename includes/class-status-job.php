<?php

namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Status_Job {
    public function run() {
        global $wpdb;
        $table = $wpdb->prefix . 'auction_listings';
        $now = current_time('mysql');

        $count = $wpdb->query(
            $wpdb->prepare("UPDATE $table SET status = 'inactive' WHERE sale_date < %s AND status = 'active'", $now)
        );

        log_debug('[Status Job] Marked inactive: ' . $count);
    }
}
