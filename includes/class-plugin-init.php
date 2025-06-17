<?php
namespace AuctionMarketplace;

defined('ABSPATH') || exit;

class Plugin_Init {

    private static $instance = null;

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(plugin_dir_path(__DIR__) . 'auction-marketplace.php', [$this, 'on_activation']);
        add_action('init', [$this, 'register_hooks']);
    }

    public function register_hooks() {
        add_filter('cron_schedules', [$this, 'add_custom_cron_interval']);
        add_action('auction_cron_event', [$this, 'handle_cron_event']);
        add_action('engine_sync_event', [$this, 'handle_engine_sync_event']);
        add_action('image_sync_event', [$this, 'handle_image_sync_event']);
        // add_action('status_sync_event', [$this, 'handle_status_sync_event']);
        
        // add_action('engine_sync_event', function () {
        //     (new \AuctionMarketplace\Engine_Job())->run();
        // });
        
        // add_action('image_sync_event', function () {
        //     (new \AuctionMarketplace\Image_Job())->run();
        // });
        
        // add_action('status_sync_event', function () {
        //     (new \AuctionMarketplace\Status_Job())->run();
        // });

        add_action('wp_footer', [self::class, 'inject_footer_code']);
        
        Shortcodes::register();
        Ajax_Handler::register();

        if (!wp_next_scheduled('auction_cron_event')) {
            wp_schedule_event(time(), 'every_5_minutes', 'auction_cron_event');
        }

        if (!wp_next_scheduled('engine_sync_event')) {
            wp_schedule_event(time(), 'every_2_minutes', 'engine_sync_event');
        }

        if (!wp_next_scheduled('image_sync_event')) {
            wp_schedule_event(time(), 'every_2_minutes', 'image_sync_event');
        }

        // if (!wp_next_scheduled('status_sync_event')) {
        //     wp_schedule_event(time(), 'every_2_minutes', 'status_sync_event');
        // }

    }

    public function add_custom_cron_interval($schedules): array {
        
        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => __('Every Minute')
        ];

        $schedules['every_2_minutes'] = [
            'interval' => 120,
            'display'  => __('Every 2 Minutes')
        ];

        $schedules['every_5_minutes'] = [
            'interval' => 300,
            'display'  => __('Every 5 Minutes')
        ];

        $schedules['every_30_minutes'] = [
            'interval' => 1800,
            'display'  => __('Every 30 Minutes')
        ];
        return $schedules;
    }
    
    public function handle_cron_event() {
        $job = new Sync_Job();
        // $job->run(); // Optional: add filters here
    }

    public function handle_engine_sync_event() {
        $job = new Engine_Job();
        // $job->run(); // Optional: add filters here
    }

    public function handle_image_sync_event() {
        $job = new Image_Job();
        $job->run(); // Optional: add filters here
    }

    // public function handle_status_sync_event() {
    //     $job = new Status_Job();
    //     $job->run(); // Optional: add filters here
    // }

    public static function inject_footer_code() {
        ?>
            <!-- Preloader -->
            <div id="auctionPreloader"></div>
            <!-- Simple Toast -->
            <div id="auctionSimpleToast"><span>The notification message...</span></div>

        <?php
    }
    
}
