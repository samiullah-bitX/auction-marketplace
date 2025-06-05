<?php
    global $wpdb;
    $table = $wpdb->prefix . 'auction_listings';
    $results = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active' ORDER BY updated_at DESC LIMIT 10");

    if (empty($results)) {
        echo '<p>No active auctions found.</p>';
        return;
    }
?>

<!-- Auction Grid -->
<section class="bitcx_amp_auctions py-5">
    <div class="container">
        <!-- Section Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="bitcx_amp_section_title h4">Auction Cars (<?php echo count($results); ?>)</h2>
            <a href="#" class="bitcx_amp_link">See All</a>
        </div>
        <!-- Cards -->
        <div class="row bitcx_amp_row g-4">
            <?php 
                foreach ($results as $car): 
                    $car_title = esc_html($car->make . ' ' . $car->model . ' ' . $car->year);
                    $primary_image_url = $car->primary_image_url ?? "https://placehold.co/845x633?text=Image+not+Available";
                    
                    // Calculate remaining time
                    $now = time(); // current time in seconds
                    $future = intval($car->sale_date / 1000); // convert ms to s
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

                    $auction_status = ($diff > 0) ? $car->status : 'inactive';
                    $classes = strtolower(esc_attr($car->auction_name)) . ' ' . strtolower(esc_attr($car->auction_name));
                    $classes = trim($classes);

                    $vehicle_url = "";
                                        
                    $auction_name = strtoupper($car->auction_name);
                    if (strpos($auction_name, 'COPART') !== false) {
                        $vehicle_url = 'https://www.copart.com/lot/' . $car->lot_number;
                    } else {
                        if ($primary_image_url != "") {
                            preg_match('/(\d+)~SID~/', $primary_image_url, $matches);
                            if (!empty($matches[1])) {
                                $item_id = substr($matches[1], 1); // Remove first digit
                                $vehicle_url = 'https://ca.iaai.com/Vehicles/VehicleDetails?itemid=' . $item_id;
                            }
                        }
                    }

                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card bitcx_amp_card h-100 <?php echo $classes; ?>">
                            <a href="<?php echo esc_url($vehicle_url); ?>" class="bitcx_amp_card_link">
                                <img src="<?php echo esc_url($primary_image_url); ?>" class="card-img-top bitcx_amp_card_img" alt="Car">
                            </a>
                            <div class="card-body bitcx_amp_card_body">
                                <h5 class="bitcx_amp_card_title"><a href="<?php echo esc_url($vehicle_url); ?>" class="bitcx_amp_card_link"><?php echo esc_attr($car_title); ?></a></h5>
                                <p class="bitcx_amp_card_subtitle small text-muted">
                                    <span class="auction_name"><?php echo esc_attr($car->auction_name) ?></span>
                                    <span class="status"><?php echo ucfirst(esc_attr($auction_status)); ?></span>
                                </p>
                                <div class="bitcx_amp_timer mb-2">
                                    Ends in: <span class="bitcx_amp_time"><?php echo $remaining_str; ?></span>
                                </div>
                                <div class="d-flex justify-content-between bid_price">
                                    <span class="bitcx_amp_price crnt_bid">Current Bid: <b><?php echo '$'.esc_attr($car->crnt_bid_price ?? 0) ?></b></span>
                                    <?php if ($car->buy_now): ?>
                                        <span class="bitcx_amp_price buy_now">Buy Now: <b><?php echo '$'.esc_attr($car->buy_now) ?></b></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>
    </div>
</section>
