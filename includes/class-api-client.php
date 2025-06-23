<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

/**
 * Central API client to interact with external auction data sources.
 */
class API_Client {

    private string $base_url = 'https://copart-iaai-api.com/api';
    private string $api_token;
    private int $timeout = 30;

    public function __construct() {
        // This token should be stored securely (e.g., WP options, ENV var)
        $this->api_token = defined('AUCTION_API_TOKEN') ? AUCTION_API_TOKEN : '96ded80c2a9dfb9fe007f4d77526f5b891609497aef159ac3ff0f8a9aa28ba2f';
    }

    /**
     * Set default parameters for specific endpoints.
     */
    private function set_default_params(string $endpoint, array $params): array {
        // Always add token if not already set
        if (!isset($params['api_token'])) {
            $params['api_token'] = $this->api_token;
        }

        if (strpos($endpoint, 'get-active-lots') !== false) {
            $defaults = [
                'per_page' => 10,
                'page' => 1,
                'car_info_vehicle_type' => "PASSENGER CAR",
                'auction_name' => 'COPART',
                'auction_names' => [
                    'COPART CANADA',
                    'IAAI CANADA'
                ],
                'without_sale_date' => 0,
                'auction_date_from' => date('Y-m-d', strtotime('+7 day')), // Set to one day ahead if not defined
            ];
            // Only set defaults if not already set
            foreach ($defaults as $key => $value) {
                if (!isset($params[$key])) {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * Internal method to make POST requests.
     */
    private function post(string $endpoint, array $params = []): ?array {
        try {
            $url = esc_url_raw($this->base_url . $endpoint);

            $params = $this->set_default_params($endpoint, $params);

            log_debug("API Request: $url with params " . json_encode($params));

            $response = wp_remote_post($url, [
                'timeout' => $this->timeout,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode($params),
            ]);

            if (is_wp_error($response)) {
                log_debug('API Error: ' . $response->get_error_message());
                return null;
            }

            // log_debug("API Response: " . json_encode($response));

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_debug('JSON Error: ' . json_last_error_msg());
                return null;
            }

            // log_debug("API Response Data: " . json_encode($data));

            return $data;
        } catch (\Throwable $e) {
            log_debug('Exception in API post: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch filtered auction lots (main listing).
     * @param array $filters [e.g., 'make' => 'BMW']
     */
    public function fetch_active_lots(array $filters = []): ?array {
        return $this->post('/v2/get-active-lots', $filters);
    }

    /**
     * Fetch engine info by VIN.
     */
    public function fetch_engine_info(string $vin): ?array {
        return $this->post('/v2/vin-decoding', ['vin' => $vin]);
    }

    /**
     * Fetch car detail info & images by VIN.
     */
    public function fetch_car_by_vin(string $vin): ?array {
        return $this->post('/v1/get-car-vin', ['vin_number' => $vin]);
    }
}