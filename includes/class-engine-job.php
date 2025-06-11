<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Engine_Job {
    public function run() {
        global $wpdb;
        $api = new API_Client();
        $table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';

        $rows = $wpdb->get_results("SELECT vin FROM $table WHERE engine_info_synced = 0 LIMIT 25");
        if (empty($rows)) {
            log_debug('[Engine Job] No records to sync.');
            return;
        }else{
            log_debug('[Engine Job] Found ' . count($rows) . ' records to sync.');
        }
        foreach ($rows as $row) {
            $engine = $api->fetch_engine_info($row->vin);
            if (!$engine) continue;

            $wpdb->update($raw_table, [
                'engine_json' => wp_json_encode($engine),
                'updated_at' => current_time('mysql')
            ], ['vin' => $row->vin]);

            $wpdb->update($table, ['engine_info_synced' => 1], ['vin' => $row->vin]);

            log_debug("[Engine Job] Synced VIN: {$row->vin}");
        }

        log_debug('[Engine Job] Synced ' . count($rows) . ' records.');
    }
}
