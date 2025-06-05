<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

/**
 * Syncs auction listings and enriches them into both main and raw DB tables.
 */
class Sync_Job {

    private API_Client $api;

    public function __construct() {
        $this->api = new API_Client();
    }

    /**
     * Run the full sync job — fetch active lots and persist to DB.
     */
    public function run(array $filters = []) {
        global $wpdb;
        $main_table = $wpdb->prefix . 'auction_listings';
        $raw_table  = $wpdb->prefix . 'auction_raw';

        try {
            $results = $this->api->fetch_active_lots($filters);

            if (!isset($results['result']) || !is_array($results['result'])) {
                log_debug('Invalid or empty result from auction API.');
                return;
            }

            // Start DB transaction
            $wpdb->query('START TRANSACTION');

            foreach ($results['result'] as $car) {
                $vin  = sanitize_text_field($car['vin']);
                $auction_name = sanitize_text_field($car['auction_name']);
                $created_at = current_time('mysql');
                $updated_at = $created_at;

                // Prepare main fields
                $main_data = [
                    'auction_name'          => $auction_name,
                    'vin'                   => $vin,
                    'make'                  => $car['make'] ?? null,
                    'model'                 => $car['model'] ?? null,
                    'year'                  => $car['year'] ?? null,
                    'location'              => $car['location'] ?? null,
                    'body_style'            => $car['body_style'] ?? null,
                    'color'                 => $car['color'] ?? null,
                    'drive'                 => $car['drive'] ?? null,
                    'lot_number'            => $car['lot_number'] ?? null,
                    'transmission'          => $car['transmission'] ?? null,
                    'fuel'                  => $car['fuel'] ?? null,
                    'odometer'              => $car['odometer'] ?? null,
                    'primary_damage'        => $car['primary_damage'] ?? null,
                    'seller'                => $car['seller'] ?? null,
                    'sale_date'             => isset($car['active_bidding'][0]['sale_date']) ? $car['active_bidding'][0]['sale_date'] : null,
                    'primary_image_url'     => $car['car_photo']['photo'][0] ?? null,
                    'crnt_bid_price'        => $car['active_bidding'][0]['current_bid'] ?? null,
                    'buy_now'               => ($car['buy_now_car'] != null && !empty($car['buy_now_car'])) ? $car['buy_now_car']["purchase_price"] : null,
                    'engine_info_synced'    => 0, // Initially not synced
                    'car_info_synced'       => 0, // Initially not synced
                    'status'                => 'active',
                    'updated_at'            => $updated_at,
                ];

                // Check if record exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $main_table WHERE vin = %s",
                    $vin
                ));

                if ($exists) {
                    // Update existing record
                    if ($wpdb->update(
                        $main_table,
                        $main_data,
                        ['vin' => $vin]
                    ) === false) {
                        throw new \Exception('Failed to update main table for VIN: ' . $vin);
                    }
                } else {
                    // Insert new record (set created_at)
                    $main_data['created_at'] = $created_at;
                    if ($wpdb->insert($main_table, $main_data) === false) {
                        throw new \Exception('Failed to insert main table for VIN: ' . $vin);
                    }
                }

                // Prepare raw table data
                $raw_data = [
                    'vin'          => $vin,
                    'raw_json'     => wp_json_encode($car),
                    'updated_at'   => $updated_at,
                ];

                // Check if raw record exists
                $raw_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $raw_table WHERE vin = %s",
                    $vin
                ));

                if ($raw_exists) {
                    // Update existing raw record
                    if ($wpdb->update(
                        $raw_table,
                        $raw_data,
                        ['vin' => $vin]
                    ) === false) {
                        throw new \Exception('Failed to update raw table for VIN: ' . $vin);
                    }
                } else {
                    // Insert new raw record (set created_at)
                    $raw_data['created_at'] = $created_at;
                    if ($wpdb->insert($raw_table, $raw_data) === false) {
                        throw new \Exception('Failed to insert raw table for VIN: ' . $vin);
                    }
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');
            log_debug('Sync job completed. Total: ' . count($results['result']));
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            log_debug('Sync job failed: ' . $e->getMessage());
        }
    }
}
