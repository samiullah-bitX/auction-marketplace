<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class S3_Sync_Job {

    public function run() {
        global $wpdb;

        $start_time = microtime(true);
        $raw_table = $wpdb->prefix . 'auction_raw';

        $rows = $wpdb->get_results(
            "SELECT vin, image_json FROM $raw_table WHERE s3_synced = 0 LIMIT 10"
        );

        if (empty($rows)) {
            log_debug('[S3 Sync Job] No records to sync.');
            return;
        }

        log_debug('[S3 Sync Job] Found ' . count($rows) . ' records to process.');

        $s3 = new Aws_S3_Helper();
        $now = current_time('mysql');

        $case_keys = '';
        $case_updated = '';
        $vins = [];

        try {
            $wpdb->query('START TRANSACTION');

            foreach ($rows as $row) {
                $vin = strtoupper(sanitize_text_field($row->vin));
                $image_json = json_decode($row->image_json ?? '{}', true);
                $photos = $image_json['result'][0]['car_photo']['photo'] ?? [];

                if (empty($photos)) {
                    log_debug("[S3 Sync Job] No photos for VIN: $vin");
                    continue;
                }

                $s3BucketKeys = $s3->send_images_to_lambda($vin, $photos);

                if (is_null($s3BucketKeys) || empty($s3BucketKeys) || !is_array($s3BucketKeys)) {
                    log_debug("[S3 Sync Job] Failed to upload images for VIN: $vin");
                    continue;
                }

                $escaped_keys = esc_sql(wp_json_encode($s3BucketKeys));
                $escaped_time = esc_sql($now);
                $case_keys    .= "WHEN '$vin' THEN '$escaped_keys' ";
                $case_updated .= "WHEN '$vin' THEN '$escaped_time' ";
                $vins[] = "'$vin'";

                log_debug("[S3 Sync Job] Uploaded " . count($s3BucketKeys) . " images for VIN: $vin");
                
            }

            if (empty($vins)) {
                $wpdb->query('ROLLBACK');
                log_debug('[S3 Sync Job] No successful uploads. Nothing updated.');
                return;
            }

            $update_sql = "
                UPDATE $raw_table
                SET
                    s3_image_keys = CASE vin $case_keys END,
                    s3_synced = 1,
                    updated_at = CASE vin $case_updated END
                WHERE vin IN (" . implode(',', $vins) . ")";

            $wpdb->query($update_sql);

            $wpdb->query('COMMIT');

            $duration = round(microtime(true) - $start_time, 2);
            log_debug("[S3 Sync Job] Synced " . count($vins) . " records in {$duration}s.");
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            log_debug('[S3 Sync Job] Error: ' . $e->getMessage());
        }
    }
}
