<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class S3_Sync_Job {

    public function run() {
        global $wpdb;

        $raw_table = $wpdb->prefix . 'auction_raw';
        $rows = $wpdb->get_results(
            "SELECT vin, image_json FROM $raw_table WHERE s3_synced = 0 LIMIT 10"
        );

        $s3 = new Aws_S3_Helper();

        foreach ($rows as $row) {
            $vin = sanitize_text_field($row->vin);
            $image_json = json_decode($row->image_json ?? '{}', true);
            $photos = $image_json['result'][0]['car_photo']['photo'] ?? [];

            if (empty($photos)) {
                log_debug("[S3 Sync Job] No photos found for VIN: $vin");
                continue; // nothing to sync
            }

            log_debug("[S3 Sync Job] Syncing VIN: $vin with " . count($photos) . " photos");

            $uploaded = [];

            foreach ($photos as $url) {
                $key = $s3->upload_image($vin, $url);
                if ($key) {
                    $uploaded[] = $key;
                }
            }

            if (!empty($uploaded)) {
                $wpdb->update($raw_table, [
                    's3_image_keys'   => wp_json_encode($uploaded),
                    's3_synced' => 1,
                    'updated_at' => current_time('mysql')
                ], ['vin' => $vin]);
            }
        }

        log_debug('[S3 Sync Job] Synced rows: ' . count($rows));
    }
}
