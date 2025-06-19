<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

/**
 * Registers and renders shortcodes for auction listings and car details.
 */
class Shortcodes {

    public static function register() {
        add_shortcode('auction_main_listing', [self::class, 'render_main_listing']);
        add_shortcode('auction_main_filters', [self::class, 'render_main_filters']);
        add_shortcode('auction_car_detail', [self::class, 'render_car_detail']);
        add_shortcode('auction_filters_listing', [self::class, 'render_listing_with_filters']);
    }

    public static function render_main_listing($atts = []) {
        ob_start();
        global $wpdb;
        $table = $wpdb->prefix . 'auction_listings';
        $results = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active' ORDER BY updated_at DESC LIMIT 10");

        if (empty($results)) {
            echo '<p>No active auctions found.</p>';
            return;
        }
        include plugin_dir_path(__DIR__) . '/templates/main-listing.php';
        return ob_get_clean();
    }

    public static function render_main_filters($atts = []) {
        ob_start();
        $vehicle_makes = Shortcodes::get_static_vehicle_makes();
        include plugin_dir_path(__DIR__) . '/templates/main-filters.php';
        return ob_get_clean();
    }

    public static function render_listing_with_filters($atts = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';
        // Build WHERE clause based on $_GET filters
        $where = [];
        $params = [];

        $copart_iaai = [];
        foreach ($_GET as $key => $value) {
            $safe_value = sanitize_text_field($value);
            $listing_params[$key] = $safe_value;

            // Only allow certain columns to be filtered for security
            // If 'vin' is present, only filter by vin and ignore other filters
            if ($key === 'vin' && !empty($safe_value)) {
                $where = ["l.vin = %s"];
                $params = [$safe_value];
                break;
            } elseif ($key === 'year_range') {
                $years = explode('-', $safe_value);
                if (count($years) == 2) {
                    $where[] = "(l.year >= %d AND l.year <= %d)";
                    $params[] = intval($years[0]);
                    $params[] = intval($years[1]);
                }
            } elseif ($key === 'bid_range') {
                $bids = explode('-', $safe_value);
                if (count($bids) == 2) {
                    $where[] = "(l.crnt_bid_price >= %d AND l.crnt_bid_price <= %d)";
                    $params[] = intval($bids[0]);
                    $params[] = intval($bids[1]);
                }
            } elseif ($key == 'copart' || $key == 'iaai') {
                if ($safe_value === '1') {
                    $copart_iaai[] = strtoupper($key);
                }
            } elseif ($key == 'archived') {
                if ($safe_value === '1') {
                    $where[] = "l.status = %s";
                    $params[] = "inactive";
                }
            } else {
                $where[] = "$key = %s";
                $params[] = $safe_value;
            }
        }

        if (!empty($copart_iaai)) {
            // Remove any previous auction_name filter to avoid conflict
            $where = array_filter($where, function($clause) {
            return strpos($clause, 'l.auction_name') === false;
            });
            $placeholders = implode(',', array_fill(0, count($copart_iaai), '%s'));
            $where[] = "l.auction_name IN ($placeholders)";
            foreach ($copart_iaai as $auction) {
            $params[] = $auction;
            }
        }

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        // Join with wp_auction_raw to get additional data (e.g., raw_json)
        $query = "SELECT l.*, r.* 
              FROM $table l 
              LEFT JOIN $raw_table r ON l.vin = r.vin 
              $where_sql 
              ORDER BY l.updated_at DESC 
              LIMIT 20";
        $sql_query = $wpdb->prepare($query, $params);
        $results = $wpdb->get_results($wpdb->prepare($query, $params));

        // Decode JSON fields if results are available
        if (!empty($results)) {
            $results = array_map(function($row) {
                if (isset($row->raw_json)) {
                    $row->raw_json = json_decode($row->raw_json, true);
                }
                if (isset($row->image_json)) {
                    $row->image_json = json_decode($row->image_json, true);
                }
                if (isset($row->engine_json)) {
                    $row->engine_json = json_decode($row->engine_json, true);
                }
                return $row;
            }, $results);
        }

        ob_start();
        include plugin_dir_path(__DIR__) . '/templates/listing-with-filters.php';
        return ob_get_clean();
    }

    public static function render_car_detail($atts = []) {
        $vin = isset($atts['vin']) ? $atts['vin'] : sanitize_text_field($_GET['vin'] ?? '');

        if (empty($vin)) return '<div class="alert alert-danger">Please Provide a VIN Number</div>';

        $atts = shortcode_atts(['vin' => ''], $vin);

        global $wpdb;
        $table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';

        $car = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT l.*, r.* 
                FROM $table l 
                LEFT JOIN $raw_table r ON l.vin = r.vin 
                WHERE l.vin = %s 
                LIMIT 1",
            $vin
            )
        );

        // Decode JSON fields if results are available
        if (!empty($car)) {
            if (isset($car->raw_json)) {
                $car->raw_json = json_decode($car->raw_json, true);
            }
            if (isset($car->image_json)) {
                $car->image_json = json_decode($car->image_json, true);
            }
            if (isset($car->engine_json)) {
                $car->engine_json = json_decode($car->engine_json, true);
            }
        }

        // Fetch 4 related cars with the same make, excluding the current car
        $related_vehicles = [];
        if (!empty($car) && !empty($car->make)) {
            $related_query = $wpdb->prepare(
                "SELECT l.*
                FROM $table l 
                WHERE l.make = %s AND l.vin != %s 
                ORDER BY l.updated_at DESC 
                LIMIT 4",
                $car->make,
            $vin
            );
            $related_vehicles = $wpdb->get_results($related_query);

            // Decode JSON fields for related cars
            if (!empty($related_vehicles)) {
                $related_vehicles = array_map(function($row) {
                    if (isset($row->raw_json)) {
                        $row->raw_json = json_decode($row->raw_json, true);
                    }
                    if (isset($row->image_json)) {
                        $row->image_json = json_decode($row->image_json, true);
                    }
                    if (isset($row->engine_json)) {
                        $row->engine_json = json_decode($row->engine_json, true);
                    }
                    return $row;
                }, $related_vehicles);
            }
        }

        ob_start();
        include plugin_dir_path(__DIR__) . '/templates/vehicle-details.php';
        return ob_get_clean();
    }

    /**
     * Get Vehicle Makes
     *
     * @return array|WP_Error Array of car types or WP_Error on failure
    */

    public static function get_static_vehicle_makes(){

        $makes = [
            "10" => "MERCEDES-BENZ",
            "20" => "BMW",
            "13" => "AUDI",
            "360" => "VW",
            "5" => "TOYOTA",
            "5939" => "OPEL",
            "711" => "PEUGEOT",
            "16" => "ACURA",
            "47" => "ALFA ROMEO",
            "48" => "ASTON MARTIN",
            "724" => "AUSTIN",
            "53" => "BENTLEY",
            "70907" => "BERTONE",
            "10814" => "BUGATTI",
            "29" => "BUICK",
            "31" => "CADILLAC",
            "837" => "CARBODIES",
            "3" => "CHEVROLET",
            "19" => "CHRYSLER",
            "72" => "DAEWOO",
            "546" => "DAIHATSU",
            "14" => "DODGE",
            "49" => "FERRARI",
            "41" => "FIAT",
            "35644" => "FISKER",
            "11" => "FORD",
            "35657" => "FOTON",
            "374" => "GEELY",
            "46" => "GENESIS",
            "6" => "GMC",
            "8" => "HONDA",
            "37" => "HUMMER",
            "12" => "HYUNDAI",
            "70802" => "INEOS GRENADEIR",
            "4" => "INFINITI",
            "26" => "ISUZU",
            "873" => "IVECO",
            "27" => "JAGUAR",
            "9" => "JEEP",
            "23" => "KIA",
            "50" => "LAMBORGHINI",
            "5938" => "LANCIA",
            // "36" => "LAND ROVER",
            "34" => "LEXUS",
            "25" => "LINCOLN",
            "57" => "LOTUS",
            "71011" => "MAHINDRA",
            "42" => "MASERATI",
            "621" => "MAYBACH",
            "2" => "MAZDA",
            "61" => "MCLAREN",
            "28" => "MERCURY",
            "649" => "MG",
            "24" => "MINI",
            "18" => "MITSUBISHI",
            "803" => "MORGAN",
            "7" => "NISSAN",
            "39" => "OLDSMOBILE",
            "17" => "PONTIAC",
            "30" => "PORSCHE",
            "70882" => "ROLLS-ROYCE",
            // "36" => "ROVER",
            "40" => "SAAB",
            "45" => "SMART",
            "15" => "SUBARU",
            "44" => "SUZUKI",
            "32" => "TESLA",
            "91" => "TRIUMPH",
            "33" => "VOLVO",
			"884" => "AC CUSTOMS",
            "143" => "DS CORP DBA CROSSROADS RV",
			"21" => "VOLKSWAGEN", // Was Available as VW in mobile.bg
			"15497" => "POLESTAR", // Was Available as POLESTER in mobile.bg	
        ];

        return $makes;

    }

    /**
     * Get Sale Ending date
     *
     * @return string|null Sale ending date in 'Y-m-d H:i:s' format or null if not available
     * @throws \Exception If the date format is invalid
    */

    public static function get_remaining_time($sale_date){
        // Calculate remaining time
        $now = time(); // current time in seconds
        $future = intval($sale_date / 1000); // convert ms to s
        $diff = $future - $now;

        if ($diff <= 0){
            $remaining_str = "Expired";
        }else{
            $days    = floor($diff / 86400);
            $hours   = floor(($diff % 86400) / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $seconds = $diff % 60;
            $remaining_str = "{$days}D {$hours}h {$minutes}m";
        };

        return $remaining_str;
    }

    /**
     * Get Sale Ending date
     *
     * @return string|null Get auction link based on auction name and primary image URL
     * @param string $auction_name The name of the auction
     * @throws \Exception If the auction name is invalid or not recognized
     * @param string|null $primary_image_url The primary image URL of the vehicle (optional)
     * @param string|null $lot_number The lot number of the vehicle (optional)
     * @return string The URL of the auction lot
    */

    public static function get_auction_link($vin, $auction_name, $primary_image_url = NULL, $lot_number = NULL){
        $vehicle_url = "";
        $auction_name = strtoupper($auction_name);
        // if (strpos($auction_name, 'COPART') !== false) {
        //     $vehicle_url = 'https://www.copart.com/lot/' . $lot_number;
        // } else {
        //     if ($primary_image_url != "") {
        //         preg_match('/(\d+)~SID~/', $primary_image_url, $matches);
        //         if (!empty($matches[1])) {
        //             $item_id = substr($matches[1], 1); // Remove first digit
        //             $vehicle_url = 'https://ca.iaai.com/Vehicles/VehicleDetails?itemid=' . $item_id;
        //         }
        //     }
        // }

        $vehicle_url = add_query_arg(['vin' => $vin], site_url('vehicle-details'));

        return $vehicle_url;
    }

    /**
     * Get Car Images Array
     *
     * @return string|null Sale ending date in 'Y-m-d H:i:s' format or null if not available
     * @throws \Exception If the date format is invalid
    */

    public static function get_car_images($car, $primary_image_url) {
        $max_images = 6; // Maximum number of images to return
        $images = [];

        // Primary: Use s3_image_keys if available
        if (!empty($car->s3_image_keys)) {
            // Assume s3_image_keys is a JSON array or comma-separated string
            $keys = is_array($car->s3_image_keys)
                ? $car->s3_image_keys
                : json_decode($car->s3_image_keys, true);

            if (!is_array($keys)) {
                // Try comma-separated fallback
                $keys = explode(',', $car->s3_image_keys);
            }

            $cloudfront_url = defined('AUCTION_CLF_URL') ? AUCTION_CLF_URL : '';
            foreach ($keys as $key) {
                $key = trim($key);
                if ($key) {
                    $images[] = rtrim($cloudfront_url, '/') . '/' . ltrim($key, '/');
                    if (count($images) >= $max_images) break;
                }
            }
        }

        // Fallback: Use image_json photos if s3_image_keys is empty
        if (empty($images) && isset($car->image_json['result'][0]['car_photo']['photo']) && is_array($car->image_json['result'][0]['car_photo']['photo'])) {
            $images = $car->image_json['result'][0]['car_photo']['photo'];
            $images = array_slice($images, 0, $max_images);
        }

        // Fallback: Use primary image URL if still empty
        if (empty($images) && $primary_image_url) {
            $images[] = $primary_image_url;
        }

        return $images;
    }

    /**
     * Fetch filtered auction listings
     *
     * @param array $filters Associative array of filters (e.g., vin, year_range, bid_range)
     * @param int $page Page number for pagination
     * @param int $per_page Number of listings per page
     * @return array Array containing 'data' and 'total' keys
     */

    public static function fetch_filtered_listings(array $filters = [], int $page = 1, int $per_page = 10): array {
        global $wpdb;
    
        $table = $wpdb->prefix . 'auction_listings';
        $raw_table = $wpdb->prefix . 'auction_raw';
    
        $where = [];
        $params = [];
    
        foreach ($filters as $key => $value) {
            $safe_value = sanitize_text_field($value);
            // Only allow certain columns to be filtered for security
            if ($key === 'vin' && !empty($safe_value)) {
                $where = ["l.vin = %s"];
                $params = [$safe_value];
                break;
            } elseif ($key === 'year_range') {
                $years = explode('-', $safe_value);
                if (count($years) == 2) {
                    $where[] = "(l.year >= %d AND l.year <= %d)";
                    $params[] = intval($years[0]);
                    $params[] = intval($years[1]);
                }
            } elseif ($key === 'bid_range') {
                $bids = explode('-', $safe_value);
                if (count($bids) == 2) {
                    $where[] = "(l.crnt_bid_price >= %d AND l.crnt_bid_price <= %d)";
                    $params[] = intval($bids[0]);
                    $params[] = intval($bids[1]);
                }
            } elseif ($key == 'copart' || $key == 'iaai') {
                if ($safe_value === '1') {
                    $copart_iaai[] = strtoupper($key);
                }
            } elseif ($key == 'archived') {
                if ($safe_value === '1') {
                    $where[] = "l.status = %s";
                    $params[] = "inactive";
                }
            } else {
                $where[] = "$key = %s";
                $params[] = $safe_value;
            }
        }

        // If copart or iaai filters are set, add them as OR condition
        if (!empty($copart_iaai)) {
            // Remove any previous auction_name filter to avoid conflict
            $where = array_filter($where, function($clause) {
            return strpos($clause, 'l.auction_name') === false;
            });
            $placeholders = implode(',', array_fill(0, count($copart_iaai), '%s'));
            $where[] = "l.auction_name IN ($placeholders)";
            foreach ($copart_iaai as $auction) {
            $params[] = $auction;
            }
        }
    
        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
        $offset = ($page - 1) * $per_page;
    
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS l.*, r.*
            FROM $table l
            LEFT JOIN $raw_table r ON l.vin = r.vin
            $where_sql
            ORDER BY l.updated_at DESC
            LIMIT %d OFFSET %d
        ";
        $params[] = $per_page;
        $params[] = $offset;
    
        $query = $wpdb->prepare($sql, $params);
        $results = $wpdb->get_results($query);

        // If results is empty or there was a DB error, return empty array and 0 total
        if (empty($results) || $wpdb->last_error) {
            return ['data' => [], 'total' => 0];
        }

        // Decode JSON fields
        $results = array_map(function($row) {
            $row->raw_json = json_decode($row->raw_json ?? '{}', true);
            $row->image_json = json_decode($row->image_json ?? '{}', true);
            $row->engine_json = json_decode($row->engine_json ?? '{}', true);
            return $row;
        }, $results);

        $total = $wpdb->get_var("SELECT FOUND_ROWS()");
    
        return ['data' => $results, 'total' => intval($total)];
    }

    /**
     * Convert timestamp to formatted date string
     *
     * @param int $timestamp Timestamp in seconds or milliseconds
     * @return string Formatted date string or 'N/A' if timestamp is empty
     */

    public static function format_sale_date($timestamp) {
        $sale_date_str = 'N/A';
        if ($timestamp) {
            if (is_numeric($timestamp) && strlen($timestamp) > 10) {
                $timestamp = intval($timestamp / 1000);
            }
            $sale_date_str = date('M d, Y H:i', $timestamp);
        }

        return $sale_date_str;
    }


    /**
     * Get the icon image path for a given drive type.
     *
     * @param string $drive_type The drive type string (e.g., 'FWD', 'AWD', etc.)
     * @return string|null The icon image path or null if not found
     */
    public static function get_drive_icon($drive_type) {
        $drive_type = strtolower(trim($drive_type));
        $drive_icon_map = [
            'fwd' => 'fwd.svg',
            'front wheel drive' => 'fwd.svg',
            'front-wheel drive' => 'fwd.svg',
            'rwd' => 'rwd.svg',
            'rear wheel drive' => 'rwd.svg',
            'rear-wheel drive' => 'rwd.svg',
            'awd' => 'awd.svg',
            '4wd' => 'awd.svg',
            'all wheel drive' => 'awd.svg',
        ];
        return $drive_icon_map[$drive_type] ?? "fwd.svg";
    }

    /**
     * Get the icon image path for a given transmission type.
     *
     * @param string $transmission_type The transmission type string (e.g., 'FWD', 'AWD', etc.)
     * @return string|null The icon image path or null if not found
     */
    public static function get_tranmission_icon($tranmission_type) {

        if (empty($tranmission_type) || !is_string($tranmission_type) || $tranmission_type == 'N/A') {
            return "automatics.svg"; // Default icon if type is empty or not a string
            # code...
        }

        $tranmission_type = strtolower(trim($tranmission_type));
        $tranmission_icon_map = [
            'automatic' => 'automatics.svg',
            'auto' => 'automatics.svg',
            'manual' => 'manual.svg',
            // 'cvt' => 'cvt.svg',
            // 'continuously variable' => 'cvt.svg',
            // 'semi-automatic' => 'semi-automatic.svg',
            // 'semi automatic' => 'semi-automatic.svg',
            // 'tiptronic' => 'tiptronic.svg',
            // 'dual clutch' => 'dual-clutch.svg',
            // 'dct' => 'dual-clutch.svg',
        ];
        return $tranmission_icon_map[$tranmission_type] ?? "automatics.svg";
    }

    /**
     * Get the icon image path for a given key type.
     *
     * @param string $key_type The key type string (e.g., 'present', 'missing', etc.)
     * @return string|null The icon image path or null if not found
     */
    public static function get_key_icon($key_type) {

        if (empty($key_type) || !is_string($key_type) || $key_type == 'N/A') {
            return "nokey.svg";
        }

        $key_type = strtolower(trim($key_type));
        $key_icon_map = [
            'present' => 'key.svg',
            'yes' => 'key.svg',
            'available' => 'key.svg',
            'missing' => 'nokey.svg',
            'no' => 'nokey.svg',
            'not available' => 'nokey.svg',
        ];
        return $key_icon_map[$key_type] ?? "nokey.svg";
    }

    /**
     * Get the icon image path for a given fuel type.
     *
     * @param string $fuel_type The fuel type string (e.g., 'gasoline', 'diesel', etc.)
     * @return string The icon image path or a default if not found
     */
    public static function get_fuel_icon($fuel_type) {

        if (empty($fuel_type) || !is_string($fuel_type) || $fuel_type == 'N/A') {
            return "nokey.svg";
        }

        $fuel_type = strtolower(trim($fuel_type));
        $fuel_icon_map = [
            'gas' => 'patrol.svg',
            'petrol' => 'patrol.svg',
            'diesel' => 'diesel.svg',
            'electric' => 'electro.svg',
            'hybrid' => 'fuel-hybrid.svg',
            'cng' => 'fuel-cng.svg',
            'lpg' => 'fuel-lpg.svg',
        ];
        return $fuel_icon_map[$fuel_type] ?? "patrol.svg";
    }
    
}
