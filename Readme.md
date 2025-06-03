# Auction Marketplace Plugin

A scalable WordPress plugin to fetch and display real-time car auctions from Copart and IAAI APIs. Designed for performance, maintainability, and modular development.

## Features

- Fetch car auctions using Copart/IAAI API.
- Store data in custom WordPress tables for efficiency.
- Schedule CRON jobs to keep listings updated.
- Display auctions with filtering and live search.
- Modular, object-oriented architecture for easy extension.

## Folder Structure

auction-marketplace/
├── auction-marketplace.php # Plugin entry point
├── includes/
│ ├── class-autoloader.php # PSR-4 style autoloader
│ ├── class-plugin-init.php # Singleton plugin core
│ ├── class-db-schema.php # Custom DB table creator
│ ├── helpers.php # Utility functions
│ └── ... # (Upcoming: API client & CRON jobs)
├── assets/
│ ├── css/ # Stylesheets
│ ├── js/ # JavaScript files
│ └── images/ # Static assets
└── uninstall.php # Optional uninstall logic


## Setup Instructions

1. Clone this repository into your `wp-content/plugins` directory.
2. Activate the plugin from the WordPress dashboard.
3. On activation, the required custom DB table is created.
4. Future jobs and UI elements will sync and display car auctions dynamically.

## Development Approach

- **OOP + Singleton Pattern** for managing plugin lifecycle.
- **Autoloading** to keep code modular and organized.
- **Custom DB Tables** for performance with large datasets.
- **WP-Cron** (planned) for background tasks like syncing auctions and images.

## Data Storage Strategy

The plugin uses a two-table system for performance and flexibility:

### Main Table: `wp_auction_listings`
Stores searchable and filterable fields:
- `vin`, `make`, `model`, `year`, `location`, `fuel`, `odometer`, `sale_date`, etc.

### Raw Table: `wp_auction_raw`
Stores:
- Full original JSON from the API
- Engine & image info (fetched later from other endpoints)

This separation allows for:
- Fast search on UI
- Rich detail display without slowing queries

## API Integration

The plugin connects to 3 endpoints via POST:

### 1. Auction Listings (`/v2/get-active-lots`)
Fetches active auctions with filtering options:
- `make`, `model`, `year_from`, `year_to`, `auction_date_from`, `auction_date_to`, etc.

### 2. VIN Engine Info (`/v2/vin-decoding`)
Used to enrich listings with manufacturing and mechanical details.

### 3. VIN Car Info (`/v1/get-car-vin`)
Returns full car details including image URLs and sales history.

All API requests use a centralized token defined in `auction-marketplace.php`:
define('AUCTION_API_TOKEN', 'your-real-token');

The API client handles:
- Request building
- Timeout and error logging
- JSON decoding

## Data Sync Job

### Class: `includes/class-sync-job.php`

Handles:
- Fetching filtered auctions from API
- Storing searchable fields in `wp_auction_listings`
- Storing full raw, engine, and image data in `wp_auction_raw`

Supports:
- Full data upsert via `wpdb->replace()`
- Future CRON or manual syncs

## Recurring Data Sync (WP-Cron)

- Interval: Every 30 minutes
- Uses WordPress Cron API
- Event: `auction_cron_event`
- Class: `Sync_Job`

To verify WP-Cron is running:
- Use CLI: `wp cron event list`
- Use plugin: WP Crontrol

## Roadmap

- [ ] Implement API Client
- [ ] Sync Jobs for Listings & Images
- [ ] Admin Dashboard for Logs/Settings
- [ ] Front-End Shortcodes & Templates
