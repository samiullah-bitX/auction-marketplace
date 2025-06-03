<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class DB_Schema {

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
    
        $main_table = $wpdb->prefix . 'auction_listings';
        $raw_table  = $wpdb->prefix . 'auction_raw';
    
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
        // Main Table - filterable fields
        $sql_main = "CREATE TABLE IF NOT EXISTS $main_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            auction_name VARCHAR(64) NOT NULL,
            vin VARCHAR(64) NOT NULL,
            make VARCHAR(64) NULL,
            model VARCHAR(64) NULL,
            year VARCHAR(64) NULL,
            location VARCHAR(255) NULL,
            body_style VARCHAR(100) NULL,
            color VARCHAR(64) NULL,
            drive VARCHAR(100) NULL,
            lot_number VARCHAR(100) NULL,
            transmission VARCHAR(100) NULL,
            fuel VARCHAR(50) NULL,
            odometer VARCHAR(64) NULL,
            primary_damage VARCHAR(100) NULL,
            seller VARCHAR(100) NULL,
            sale_date DATETIME NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY vin (vin)
        ) $charset;";

        // Raw Table - full JSON + enrichments
        $sql_raw = "CREATE TABLE IF NOT EXISTS $raw_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            vin VARCHAR(64) NOT NULL,
            raw_json LONGTEXT NULL,
            engine_json LONGTEXT NULL,
            image_json LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY vin (vin)
        ) $charset;";
    
        dbDelta($sql_main);
        dbDelta($sql_raw);
    }
    
}
