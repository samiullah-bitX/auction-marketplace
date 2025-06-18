<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Image_Job {
    public function run() {
        global $wpdb;
        $api = new API_Client();
        $table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';
        $s3 = new Aws_S3_Helper();
        $uploaded_keys = [];

        $rows = $wpdb->get_results("SELECT vin FROM $table WHERE car_info_synced = 0 LIMIT 25");
        if (empty($rows)) {
            log_debug('[Image Job] No records to sync.');
            return;
        }
        foreach ($rows as $row) {
            $image_data = $api->fetch_car_by_vin($row->vin);
            if (!$image_data) continue;

            $wpdb->update($raw_table, [
                'image_json' => wp_json_encode($image_data),
                'updated_at' => current_time('mysql')
            ], ['vin' => $row->vin]);
            
            $wpdb->update($table, ['car_info_synced' => 1], ['vin' => $row->vin]);
            
            log_debug("[Image Job] Synced VIN: {$row->vin}");
        }

        log_debug('[Image Job] Synced ' . count($rows) . ' records.');
    }
}
