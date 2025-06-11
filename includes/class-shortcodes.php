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
        // Build WHERE clause based on $_GET filters
        $where = [];
        $params = [];
        $allowed = [];

        foreach ($_GET as $key => $value) {
            $safe_value = sanitize_text_field($value);
            $listing_params[$key] = $safe_value;

            // Only allow certain columns to be filtered for security
            // If 'vin' is present, only filter by vin and ignore other filters
            if ($key === 'vin' && !empty($safe_value)) {
                $where = ["vin = %s"];
                $params = [$safe_value];
                break;
            } elseif ($key === 'year_range' && !empty($safe_value)) {
                // Expecting format: "YYYY-YYYY"
                $years = explode('-', $safe_value);
                if (count($years) == 2) {
                    $where[] = "(year >= %d AND year <= %d)";
                    $params[] = intval($years[0]);
                    $params[] = intval($years[1]);
                }
            } elseif ($key === 'bid_range' && !empty($safe_value)) {
                // Expecting format: "min-max"
                $bids = explode('-', $safe_value);
                if (count($bids) == 2) {
                    $where[] = "(current_bid >= %d AND current_bid <= %d)";
                    $params[] = intval($bids[0]);
                    $params[] = intval($bids[1]);
                }
                $where[] = "$key = %s";
            }else{
                $where[] = "$key = %s";
            }
        }

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        // Join with wp_auction_raw to get additional data (e.g., raw_json)
        $raw_table = $wpdb->prefix . 'auction_raw';
        $query = "SELECT l.*, r.raw_json, r.engine_json, r.image_json 
              FROM $table l 
              LEFT JOIN $raw_table r ON l.vin = r.vin 
              $where_sql 
              ORDER BY l.updated_at DESC 
              LIMIT 20";
        $sql_query = $wpdb->prepare($query, $params);
        $results = $wpdb->get_results($wpdb->prepare($query, $params));

        error_log("results: ". print_r($results, true));

        ob_start();
        // Make $params available in the template
        // $listing_params = $params;
        include plugin_dir_path(__DIR__) . '/templates/listing-with-filters.php';
        return ob_get_clean();
    }

    public static function render_car_detail($atts = []) {
        $atts = shortcode_atts(['vin' => ''], $atts);

        if (empty($atts['vin'])) return '<p>No VIN provided.</p>';

        global $wpdb;
        $table = $wpdb->prefix . 'auction_raw';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE vin = %s", $atts['vin']), ARRAY_A);

        if (!$row) return '<p>Car not found.</p>';

        $car = json_decode($row['raw_json'], true);

        ob_start();
        include plugin_dir_path(__DIR__) . '/templates/detail.php';
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
            "36" => "LAND ROVER",
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
            "36" => "ROVER",
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
     * @return string|null Sale ending date in 'Y-m-d H:i:s' format or null if not available
     * @throws \Exception If the date format is invalid
    */

    public static function get_auction_link($auction_name, $primary_image_url = NULL, $lot_number = NULL){
        $vehicle_url = "";
        $auction_name = strtoupper($auction_name);
        if (strpos($auction_name, 'COPART') !== false) {
            $vehicle_url = 'https://www.copart.com/lot/' . $lot_number;
        } else {
            if ($primary_image_url != "") {
                preg_match('/(\d+)~SID~/', $primary_image_url, $matches);
                if (!empty($matches[1])) {
                    $item_id = substr($matches[1], 1); // Remove first digit
                    $vehicle_url = 'https://ca.iaai.com/Vehicles/VehicleDetails?itemid=' . $item_id;
                }
            }
        }

        return $vehicle_url;
    }
}
