<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

/**
 * Handles AJAX requests for filtering listings.
 */
class Ajax_Handler {

    public static function register() {
        add_action('wp_ajax_filter_auctions', [self::class, 'filter_auctions']);
        add_action('wp_ajax_nopriv_filter_auctions', [self::class, 'filter_auctions']);
        add_action('wp_ajax_get_vehicle_model_by_make', [self::class, 'get_vehicle_model_by_make_id']);
        add_action('wp_ajax_nopriv_get_vehicle_model_by_make', [self::class, 'get_vehicle_model_by_make_id']);
        add_action('wp_ajax_paginate_auctions', [self::class, 'paginate']);
        add_action('wp_ajax_nopriv_paginate_auctions', [self::class, 'paginate']);
    }

    public static function filter_auctions() {
        global $wpdb;

        $make  = sanitize_text_field($_POST['make'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? '');
        $year  = intval($_POST['year'] ?? 0);

        $query = "SELECT * FROM {$wpdb->prefix}auction_listings WHERE status = 'active'";
        if ($make) $query .= $wpdb->prepare(" AND make = %s", $make);
        if ($model) $query .= $wpdb->prepare(" AND model = %s", $model);
        if ($year) $query .= $wpdb->prepare(" AND year = %d", $year);
        $query .= " ORDER BY updated_at DESC LIMIT 20";

        $results = $wpdb->get_results($query);

        // Return JSON response
        wp_send_json_success(['cars' => $results]);
    }

    /**
     * Get Vehicle Models from API
     *
     * @return array|WP_Error Array of car types or WP_Error on failure
    */

    // public static function get_vehicle_model_by_make_id($makeID= 0) {
        
    //     $isAjaxRequest = false;

    //     if (!$makeID) {
    //         $isAjaxRequest = true;
    //         $makeID = $_POST['vehicle_make_id'] ?? 0;
    //     }

    //     if (!$makeID) {
    //         log_debug("No make ID provided.");
    //         wp_send_json_error("Не е предоставен идентификатор на марката на превозното средство");
    //         return false;
    //     }

    //     // Check if we have cached data
    //     $cached_vehicle_models = get_transient('auction_vehicle_models_of_make_id_'.$makeID);

    //     if (false !== $cached_vehicle_models) {
    //         if ($isAjaxRequest) 
    //             wp_send_json_success($cached_vehicle_models);
    //         else 
    //             return $cached_vehicle_models;
    //     }

    //     $api_token = defined('AUCTION_API_TOKEN') ? AUCTION_API_TOKEN : '';
    //     $api_base_url_v1 = defined('AUCTION_BASE_URL_V1') ? AUCTION_BASE_URL_V1 : '';
    //     $url = $api_base_url_v1.'/get-model-by-make/'.$makeID.'?api_token='.$api_token;

    //     $response = wp_remote_post($url, array(
    //         'timeout' => 30,
    //     ));

    //     // log_debug(print_r($response, true)); // Debugging line

    //     if (is_wp_error($response)) {
    //         if ($isAjaxRequest) {
    //             wp_send_json_error("Грешка при зареждането на моделите на превозните средства");
    //         } else {
    //             return $response;
    //         }
    //     }

    //     $body = wp_remote_retrieve_body($response);
    //     $response_data = json_decode($body, true);

    //     // Extract only id and vehicle_makes pairs
    //     $data = array();
    //     if (isset($response_data['result']) && is_array($response_data['result'])) {
    //         foreach ($response_data['result'] as $item) {
    //             if (!empty($item["model"])) {
    //                 $data[$item['id']] = $item['model'];
    //             }
    //         }
    //     }else{
    //         wp_send_json_error("Не е намерен модел на превозното средство");
    //     }

    //     // Cache the data for 3 days
    //     set_transient('auction_vehicle_models_of_make_id_'.$makeID, $data, 3 * DAY_IN_SECONDS);

    //     if ($isAjaxRequest) {
    //         // Return the data as a JSON response for AJAX requests
    //         wp_send_json_success($data);
    //     } else {
    //         // Return the data for non-AJAX requests
    //         return $data;
    //     }

    // }

    public static function get_vehicle_model_by_make_id($makeID = 0) {
        global $wpdb;

        $isAjaxRequest = false;

        if (!$makeID) {
            $isAjaxRequest = true;
            $makeID = $_POST['vehicle_make_id'] ?? 0;
        }

        $makeID = intval($makeID);

        if (!$makeID) {
            log_debug("No make ID provided.");
            wp_send_json_error("Не е предоставен идентификатор на марката на превозното средство");
            return false;
        }

        // Check if we have cached data
        $cached_vehicle_models = get_transient('auction_vehicle_models_of_make_id_' . $makeID);

        if (false !== $cached_vehicle_models) {
            if ($isAjaxRequest)
                wp_send_json_success($cached_vehicle_models);
            else
                return $cached_vehicle_models;
        }

        // Query the database for models by make_id
        $table_name = $wpdb->prefix . 'auction_models';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT id, model FROM $table_name WHERE make_id = %d", $makeID),
            ARRAY_A
        );

        $data = array();
        if (!empty($results)) {
            foreach ($results as $item) {
                if (!empty($item["model"])) {
                    $data[$item['id']] = $item['model'];
                }
            }
        } else {
            wp_send_json_error("Не е намерен модел на превозното средство");
        }

        // Cache the data for 3 days
        set_transient('auction_vehicle_models_of_make_id_' . $makeID, $data, 3 * DAY_IN_SECONDS);

        if ($isAjaxRequest) {
            wp_send_json_success($data);
        } else {
            return $data;
        }
    }

    /**
     * Handles pagination for auction listings.
     * @return void
     */

    public static function paginate() {
        check_ajax_referer('vehicle-pagination-nonce');
    
        $filters = $_POST['filters'] ?? [];
        $page = intval($_POST['page'] ?? 1);
    
        $result = \AuctionMarketplace\Shortcodes::fetch_filtered_listings($filters, $page);
        // error_log("Result: ".print_r($result, true)); // Debugging line
        $cars = $result['data'];
        $total = $result['total'];
        $per_page = 10;
        $pages = ceil($total / $per_page);

        ob_start();
        include plugin_dir_path(__DIR__) . 'templates/partials/listing-loop.php';
        $html = ob_get_clean();
    
        wp_send_json_success([
            'html' => $html,
            'total_pages' => $pages,
            'current_page' => $page
        ]);
    }
}
