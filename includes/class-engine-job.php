<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Engine_Job {

    public function run() {
        global $wpdb;

        $start_time = microtime(true);
        $api = new API_Client();
        $listings_table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';

        $rows = $wpdb->get_results("SELECT vin FROM $listings_table WHERE engine_info_synced = 0 LIMIT 25");

        if (empty($rows)) {
            log_debug('[Engine Job] No records to sync.');
            return;
        }

        log_debug('[Engine Job] Found ' . count($rows) . ' records to sync.');

        try {
            $wpdb->query('START TRANSACTION');

            $case_engine = '';
            $case_updated = '';
            $vins = [];
            $now = current_time('mysql');

            foreach ($rows as $row) {
                $vin = strtoupper($row->vin);
                $engine = $api->fetch_engine_info($vin);

                if (!$engine || !is_array($engine)) {
                    log_debug("[Engine Job] Skipped VIN (no data): $vin");
                    continue;
                }

                $json = wp_json_encode($engine);
                $escaped_json = esc_sql($json);
                $escaped_time = esc_sql($now);

                $case_engine  .= "WHEN '$vin' THEN '$escaped_json' ";
                $case_updated .= "WHEN '$vin' THEN '$escaped_time' ";
                $vins[] = "'$vin'";
            }

            if (empty($vins)) {
                $wpdb->query('ROLLBACK');
                log_debug('[Engine Job] No successful VINs to update.');
                return;
            }

            // Batch update raw table
            $update_sql = "
                UPDATE $raw_table
                SET
                    engine_json = CASE vin $case_engine END,
                    updated_at  = CASE vin $case_updated END
                WHERE vin IN (" . implode(',', $vins) . ")
            ";
            $wpdb->query($update_sql);

            // Batch update listing table
            $listing_sql = "
                UPDATE $listings_table
                SET engine_info_synced = 1
                WHERE vin IN (" . implode(',', $vins) . ")
            ";
            $wpdb->query($listing_sql);

            $wpdb->query('COMMIT');

            $duration = round(microtime(true) - $start_time, 2);
            log_debug("[Engine Job] Synced " . count($vins) . " VINs in {$duration}s.");
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            log_debug('[Engine Job] Error: ' . $e->getMessage());
        }
    }
}
