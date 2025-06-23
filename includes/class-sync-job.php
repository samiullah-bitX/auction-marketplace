<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

/**
 * Syncs auction listings from API into the WordPress database.
 * Ensures duplicate records are updated, and avoids resyncing if already complete.
 */
class Sync_Job {

    private API_Client $api;

    public function __construct() {
        $this->api = new API_Client();
    }

    public function run(array $filters = []) {
        global $wpdb;

        $main_table = $wpdb->prefix . 'auction_listings';
        $raw_table  = $wpdb->prefix . 'auction_raw';
        $start_time = microtime(true);

        $page_key = 'auction_sync_current_page';
        $done_key = 'auction_sync_done';

        // Stop if previously completed
        if (get_option($done_key) === 'yes') {
            log_debug('Sync skipped — already marked as complete.');
            return;
        }

        try {
            $page = intval(get_option($page_key));
            if (empty($page) || $page < 1) {
                $page = 1;
            }
            $filters['per_page'] = 50;
            $filters['page'] = $page;

            log_debug("Starting sync for page $page with filters: " . json_encode($filters));

            $response = $this->api->fetch_active_lots($filters);
            $results = $response['result'] ?? [];
            $total_pages = $response['pagination']['total_pages'] ?? 1;

            if (empty($results)) {
                log_debug("Page $page returned no results. Marking sync as complete.");
                delete_option($page_key);
                update_option($done_key, 'yes');
                update_option('auction_sync_done_at', current_time('mysql'));
                $duration = round(microtime(true) - $start_time, 2);
                log_debug(" Processing Took:  {$duration}s. ");
                return;
            }

            $main_rows = [];
            $main_params = [];
            $raw_rows = [];
            $raw_params = [];
            $now = current_time('mysql');

            foreach ($results as $car) {
                $vin = strtoupper(preg_replace('/[^A-Z0-9]/', '', $car['vin']));
                $sale_date = isset($car['active_bidding'][0]['sale_date']) 
                    ? date('Y-m-d H:i:s', $car['active_bidding'][0]['sale_date'] / 1000)
                    : null;

                // Main table values
                $main_rows[] = "(%s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %d, %d, %s, %s, %s)";
                array_push($main_params,
                    $car['auction_name'], $vin, $car['make'] ?? null, $car['model'] ?? null, intval($car['year'] ?? 0),
                    $car['location'] ?? null, $car['body_style'] ?? null, $car['color'] ?? null, $car['drive'] ?? null,
                    $car['lot_number'] ?? null, $car['transmission'] ?? null, $car['fuel'] ?? null, intval($car['odometer'] ?? 0),
                    $car['primary_damage'] ?? null, $car['seller'] ?? null,
                    intval($car['active_bidding'][0]['current_bid'] ?? 0),
                    intval($car['buy_now_car']['purchase_price'] ?? 0),
                    $sale_date,
                    $car['car_photo']['photo'][0] ?? null,
                    $now
                );

                // Raw table values
                $raw_rows[] = "(%s, %s, %s, %s)";
                array_push($raw_params, $vin, wp_json_encode($car), $now, $now);
            }

            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Insert/Update main table
            if (!empty($main_rows)) {
                $sql = "
                    INSERT INTO $main_table (
                        auction_name, vin, make, model, year, location, body_style, color,
                        drive, lot_number, transmission, fuel, odometer, primary_damage,
                        seller, crnt_bid_price, buy_now, sale_date, primary_image_url, created_at
                    ) VALUES " . implode(',', $main_rows) . "
                    ON DUPLICATE KEY UPDATE
                        auction_name = VALUES(auction_name),
                        make = VALUES(make),
                        model = VALUES(model),
                        year = VALUES(year),
                        location = VALUES(location),
                        body_style = VALUES(body_style),
                        color = VALUES(color),
                        drive = VALUES(drive),
                        lot_number = VALUES(lot_number),
                        transmission = VALUES(transmission),
                        fuel = VALUES(fuel),
                        odometer = VALUES(odometer),
                        primary_damage = VALUES(primary_damage),
                        seller = VALUES(seller),
                        crnt_bid_price = VALUES(crnt_bid_price),
                        buy_now = VALUES(buy_now),
                        sale_date = VALUES(sale_date),
                        primary_image_url = VALUES(primary_image_url),
                        updated_at = '$now'
                ";
                $wpdb->query($wpdb->prepare($sql, ...$main_params));
            }

            // Insert/Update raw table
            if (!empty($raw_rows)) {
                $sql = "
                    INSERT INTO $raw_table (
                        vin, raw_json, created_at, updated_at
                    ) VALUES " . implode(',', $raw_rows) . "
                    ON DUPLICATE KEY UPDATE
                        raw_json = VALUES(raw_json),
                        updated_at = VALUES(updated_at)
                ";
                $wpdb->query($wpdb->prepare($sql, ...$raw_params));
            }

            // Move to next page or finish
            if ($page < $total_pages) {
                update_option($page_key, $page + 1);
            } else {
                delete_option($page_key);
                update_option($done_key, 'yes');
                update_option('auction_sync_done_at', $now);
                log_debug("Sync finished — all $total_pages pages processed.");
            }

            $wpdb->query('COMMIT');

            $duration = round(microtime(true) - $start_time, 2);
            log_debug("Page $page synced in {$duration}s. Rows: " . count($results));
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            log_debug('Sync failed: ' . $e->getMessage());
        }
    }
}