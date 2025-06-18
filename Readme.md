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

## Recurring Data Sync (WP-Cron)

- Interval: Every 30 minutes
- Uses WordPress Cron API
- Event: `auction_cron_event`
- Class: `Sync_Job`

To verify WP-Cron is running:
- Use CLI: `wp cron event list`
- Use plugin: WP Crontrol

### Lightweight Cron Sync Jobs

| Job              | Interval    | Purpose                                     |
|------------------|-------------|---------------------------------------------|
| `engine_sync_event` | Every minute | Processes 1 unsynced VIN for engine info   |
| `image_sync_event`  | Every minute | Processes 1 unsynced VIN for car images    |

This approach:
- Minimizes API strain
- Ensures async, safe enrichment
- Avoids duplicate processing via flag updates

## Front-End Shortcodes

Embed these in any WordPress post/page:

- `[auction_listings]` – Displays the latest active listings
- `[auction_car_detail vin="..."]` – Displays a detailed view for a specific VIN

Templates are located in:
- `/templates/listings.php`
- `/templates/detail.php`

## AWS S3 Image Upload

The plugin uses the AWS PHP SDK to upload car photos to Amazon S3.

- Bucket: defined via `AUCTION_S3_BUCKET`
- Keys: stored in `s3_image_keys` (JSON) in `wp_auction_raw`
- Photos uploaded to folders named after each VIN

### Config (add to `wp-config.php`):

```php
define('AUCTION_S3_KEY', 'your-key');
define('AUCTION_S3_SECRET', 'your-secret');
define('AUCTION_S3_BUCKET', 'your-bucket');
define('AUCTION_S3_REGION', 'us-east-1');
```

## Image Upload to S3 (Batch Job)

A CRON job `s3_sync_event` processes image uploads from `auction_raw.image_json`:

- Uploads each image to: `vin/image-hash.jpg`
- Stores uploaded keys in: `s3_keys` column (JSON)
- Marks record as `s3_synced = 1` when complete

### Cron Hook
`s3_sync_event` — runs every 2 minutes, 10 records per batch
