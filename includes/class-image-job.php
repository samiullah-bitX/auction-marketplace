<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Image_Job {

    public function run() {
        global $wpdb;

        $start_time = microtime(true);
        $api = new API_Client();
        $listings_table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';

        $rows = $wpdb->get_results("SELECT vin FROM $listings_table WHERE car_info_synced = 0 LIMIT 25");

        if (empty($rows)) {
            log_debug('[Image Job] No records to sync.');
            return;
        }

        log_debug('[Image Job] Found ' . count($rows) . ' records to sync.');

        try {
            $wpdb->query('START TRANSACTION');

            $case_image = '';
            $case_updated = '';
            $vins = [];
            $now = current_time('mysql');

            foreach ($rows as $row) {
                $vin = strtoupper($row->vin);
                $image_data = $api->fetch_car_by_vin($vin);

                if (!$image_data || !is_array($image_data)) {
                    log_debug("[Image Job] Skipped VIN (no data): $vin");
                    continue;
                }

                $json = wp_json_encode($image_data);
                $escaped_json = esc_sql($json);
                $escaped_time = esc_sql($now);

                $case_image .= "WHEN '$vin' THEN '$escaped_json' ";
                $case_updated .= "WHEN '$vin' THEN '$escaped_time' ";
                $vins[] = "'$vin'";
            }

            if (empty($vins)) {
                $wpdb->query('ROLLBACK');
                log_debug('[Image Job] No successful VINs to update.');
                return;
            }

            $update_raw_sql = "
                UPDATE $raw_table
                SET
                    image_json = CASE vin $case_image END,
                    updated_at = CASE vin $case_updated END
                WHERE vin IN (" . implode(',', $vins) . ")
            ";
            $wpdb->query($update_raw_sql);

            $update_listing_sql = "
                UPDATE $listings_table
                SET car_info_synced = 1
                WHERE vin IN (" . implode(',', $vins) . ")
            ";
            $wpdb->query($update_listing_sql);

            $wpdb->query('COMMIT');

            $duration = round(microtime(true) - $start_time, 2);
            log_debug("[Image Job] Synced " . count($vins) . " records in {$duration}s.");
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            log_debug('[Image Job] Error: ' . $e->getMessage());
        }
    }
}