<?php

namespace AuctionMarketplace;

// Ensure WordPress environment is loaded
if (!defined('ABSPATH')) {
    require_once dirname(__FILE__, 4) . '/wp-load.php';
}

// use wpdb;

// Ensure this file is executed in a WordPress environment
// defined('ABSPATH') || exit;

class CronCreateTables {

    private $wpdb;

    public function __construct() {
        global $wpdb;
        if (!isset($wpdb)) {
            throw new \Exception('WordPress environment not loaded.');
        }
        $this->wpdb = $wpdb;
    }

    public function run() {
        error_log("We are rnning the CronCreateTables job...");
        try {
            error_log("We are trying");
            $this->createMakesTable();
            $this->insertMakes();
            $this->createModelsTable();
            $this->fetchAndInsertModels();
        } catch (\Exception $e) {
            error_log('Error in CronCreateTables: ' . $e->getMessage());
        }
    }

    private function createMakesTable() {
        // global $wpdb;
        error_log("Creating auction makes table...");
        $table_name = $this->wpdb->prefix . 'auction_makes';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            make_id BIGINT UNSIGNED NOT NULL,
            make VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private function insertMakes() {
        $table_name = $this->wpdb->prefix . 'auction_makes';
        $makes = Shortcodes::get_static_vehicle_makes();

        foreach ($makes as $make_id => $make) {
            $exists = $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE make_id = %d", $make_id));
            if (!$exists) {
                $this->wpdb->insert($table_name, [
                    'make_id' => $make_id,
                    'make' => $make
                ]);
            }
        }
    }

    private function createModelsTable() {
        $table_name = $this->wpdb->prefix . 'auction_models';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            make_id BIGINT UNSIGNED NOT NULL,
            model_id BIGINT UNSIGNED NOT NULL,
            model VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private function fetchAndInsertModels() {
        $makes = Shortcodes::get_static_vehicle_makes();
        foreach ($makes as $make_id => $make) {
            try {
                $models = Ajax_Handler::get_vehicle_model_by_make_id($make_id);
                if (is_array($models)) {
                    $this->insertModels($make_id, $models);
                }
            } catch (\Exception $e) {
                error_log("Error fetching models for make_id $make_id: " . $e->getMessage());
            }
        }
    }

    private function insertModels($make_id, $models) {
        $table_name = $this->wpdb->prefix . 'auction_models';

        foreach ($models as $model_id => $model) {
            $exists = $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE make_id = %d AND model_id = %d", $make_id, $model_id));
            if (!$exists) {
                $this->wpdb->insert($table_name, [
                    'make_id'   => $make_id,
                    'model_id'  => $model_id,
                    'model'     => $model
                ]);
            }
        }
    }
}

// Run the cron job
$cron = new CronCreateTables();
$cron->run();