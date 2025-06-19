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
            $option_key = 'auction_sync_current_page';
            $page = intval(get_option($option_key, 1));

            $filters['per_page'] = 10;
            $filters['page'] = $page;

            $response = $this->api->fetch_active_lots($filters);

            if (!isset($response['result']) || !is_array($response['result'])) {
                log_debug('Invalid or empty result from auction API.');
                delete_option($option_key); // reset for next time
                return;
            }

            $total_pages = $response['pagination']['total_pages'] ?? 1;

            // Start DB transaction
            $wpdb->query('START TRANSACTION');

            foreach ($response['result'] as $car) {
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
        
            log_debug("Initial sync completed for {$total_pages} pages.");
            
            // Move to next page, or reset
            if ($page < $total_pages) {
                update_option($option_key, $page + 1);
            } else {
                delete_option($option_key); // Sync complete
                log_debug("Initial full sync completed.");
            }

            // Commit transaction
            $wpdb->query('COMMIT');
            log_debug('Sync job completed. Total: ' . count($response['result']));
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            log_debug('Sync job failed: ' . $e->getMessage());
        }
    }
}
