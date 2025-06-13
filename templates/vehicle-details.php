<section class="car-details-wrapper">

    <?php if(!empty($car)) : 
        $car_title = esc_html($car->make . ' ' . $car->model . ' ' . $car->year);
        $primary_image_url = $car->primary_image_url ?? "https://placehold.co/845x633?text=Image+not+Available";
        $auction_name = strtoupper($car->auction_name);
        
        $images = AuctionMarketplace\Shortcodes::get_car_images($car, $primary_image_url);
        $remaining_str = AuctionMarketplace\Shortcodes::get_remaining_time($car->sale_date);
        $sale_date_str = AuctionMarketplace\Shortcodes::format_sale_date($car->sale_date);
        $vehicle_url = AuctionMarketplace\Shortcodes::get_auction_link($car->auction_name, $primary_image_url, $car->lot_number ?? null);
        
        $auction_status = ($remaining_str != "Expired") ? $car->status : 'inactive';
        $classes = strtolower(esc_attr($car->auction_name)) . ' ' . strtolower(esc_attr($auction_status));
        $classes = trim($classes);

        $carousel_id = 'carCarousel_' . md5($car->vin . $car->lot_number);
        $isCopart = str_contains(strtolower($car->auction_name), 'copart');
        
        // Convert miles to kilometers for Copart vehicles
        $odometer_value = isset($car->odometer) ? $car->odometer : 'N/A';
        if ($isCopart && is_numeric($odometer_value)) {
            $odometer_value = round($odometer_value * 1.60934); // Convert miles to kilometers
        }
    ?>

        <!-- Top Car Detail Section -->
        <div class="container py-3 border-bottom bitcx_amp_top_info">
            <div class="row align-items-center g-3">
                <!-- Left Info -->
                <div class="col-md-8">
                    <div class="d-flex align-items-center flex-wrap">
                        <h5 class="mb-0 me-2 fw-semibold"><?php echo esc_attr($car_title); ?></h5>
                        <span class="badge bg-light text-dark border me-2"><?php echo esc_attr($car->vin); ?></span>
                        <span class="badge <?php echo esc_attr($isCopart ? "bg-primary" : "bg-danger"); ?> text-white"><?php echo esc_attr($auction_name); ?></span>
                    </div>
                    <div class="text-muted mt-2 small">
                        <span class="me-3">Location: <strong class="text-primary"><?php echo esc_attr($car->location); ?></strong></span>
                        <span class="me-3">Shipping from: <strong class="text-primary">Savannah (GA)</strong></span>
                        <span>Odometer [km]: ~<strong><?php echo esc_attr($odometer_value ?? "N/A"); ?></strong></span>
                    </div>
                </div>

                <!-- Right Info -->
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block me-4">
                        <span class="text-muted small">Auction date</span><br>
                        <span class="text-danger fw-semibold"> <?php echo esc_html($sale_date_str); ?></span> 
                        <!-- <span class="text-muted small">(yesterday)</span> -->
                    </div>
                </div>
            </div>

            <!-- Bottom Buttons Row -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <button class="btn btn-link text-decoration-none me-2">
                        <i class="bi bi-graph-up-arrow me-1"></i> Sales History <span class="badge bg-light text-dark"><?php echo ($car->image_json != NULL) ? count($car->image_json["result"][0]["sales_history"]) : 0 ; ?></span>
                    </button>
                    <!-- <button class="btn btn-link text-decoration-none">
                        <i class="bi bi-box-arrow-in-down-right me-1"></i> Similar archival offers
                    </button> -->
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="container bitcx_amp_main_container">
            <div class="row g-4 py-3">
                <!-- Left: Image Gallery -->
                <div class="col-lg-5">
                    <!-- Bootstrap Carousel for Vehicle Images -->
                    <div id="<?php echo esc_attr($carousel_id); ?>" class="carousel slide bitcx_amp_slider_main" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($images as $idx => $img_url): ?>
                                <div class="carousel-item<?php if ($idx === 0) echo ' active'; ?>">
                                    <img src="<?php echo esc_url($img_url); ?>" class="d-block w-100" alt="Car Image <?php echo $idx + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <div class="carousel-nav navigation">
                                <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="bitcx_amp_thumb_gallery mt-3 d-flex justify-content-center gap-2">
                            <?php foreach ($images as $idx => $img_url): ?>
                                <img 
                                    src="<?php echo esc_url($img_url); ?>" 
                                    class="bitcx_amp_thumb_img<?php if ($idx === 0) echo ' active'; ?>" 
                                    style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                    data-bs-target="#<?php echo esc_attr($carousel_id); ?>" 
                                    data-bs-slide-to="<?php echo $idx; ?>" 
                                    <?php if ($idx === 0) echo 'aria-current="true"'; ?> 
                                    aria-label="Slide <?php echo $idx + 1; ?>"
                                >
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Vehicle Details -->
                <div class="col-lg-4 col-md">
                    <div class="bitcx_amp_vehicle_info">
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Lot:</span> <?php echo esc_attr($car->lot_number); ?>
                        </div>
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Seller:</span> <?php echo ($car->seller ? esc_attr($car->seller) : "N/A"); ?>
                        </div>
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Sale Document:</span> <span class="badge bg-success"><?php echo esc_attr($car->raw_json["doc_type"] ?? "N/A") ?></span>
                        </div>
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Odometer [Km]:</span> <?php echo esc_attr($odometer_value ?? "N/A"); ?>
                        </div>
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Primary Damage:</span> <?php echo esc_attr($car->primary_damage); ?>
                        </div>
                        <div class="bitcx_amp_info_row">
                            <span class="bitcx_amp_info_label">Start Code:</span> <span class="text-success fw-semibold">
                                <?php echo esc_attr($car->drive); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Bidding Box -->
                    <div class="bitcx_amp_bid_box">
                        <h5 class="fw-semibold mb-3">Current Bid: <span class="text-success">$<?php echo esc_attr($car->crnt_bid_price); ?> USD</span></h5>
                        <div class="input-group mb-3">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" placeholder="Enter bid amount" value="500">
                        </div>
                        <button class="btn btn-success">Bid Now</button>
                    </div>

                    <!-- Final Price Estimator -->
                    <div class="bitcx_amp_price_estimator">
                        <div class="bitcx_amp_price_box">
                            <h6 class="fw-semibold">Estimated Total Price:</h6>
                            <p><strong>$ <?php echo esc_attr($car->raw_json["est_retail_value"] ?? 0) ?></strong></p>
                            <!-- <ul class="list-unstyled">
                                <li>Purchase amount: $700 - $1,250</li>
                                <li>Customs value: $2,530 - $3,175</li>
                            </ul> -->
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <?php if ($car->buy_now): ?>
                        <!-- Final Bid Box -->
                        <div class="border border-danger rounded p-3 text-center mb-4 bitcx_amp_final_bid_box">
                            <div class="text-danger fw-semibold mb-1">Final bid</div>
                            <div class="fs-3  text-dark mb-3">$675 USD</div>
                            <button class="btn btn-danger w-100">
                                <i class="bi bi-trash-fill me-1"></i> Remove Vehicle History
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Final Price Estimator -->
                    <div class="card shadow-sm mb-4 bitcx_amp_price_estimator">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div><i class="bi bi-tags-fill me-1"></i> Final Price Estimator</div>
                            <select class="form-select form-select-sm w-auto">
                                <option selected>EUR</option>
                                <option>USD</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <!-- Bid Input Controls -->
                            <div class="input-group mb-3 justify-content-center">
                                <button class="btn btn-outline-secondary">−</button>
                                <div class="form-control text-center fw-semibold" style="max-width: 100px;">$1,175</div>
                                <button class="btn btn-outline-secondary">+</button>
                            </div>
                            <div class="text-center mb-3">
                                <span class="badge bg-danger">Your bid</span>
                            </div>

                            <!-- Total Price Summary Box -->
                            <div class="bg-light rounded p-3 text-center">
                                <h5 class="fw-bold text-primary mb-3">€4,572</h5>
                                <!-- <div class="text-muted small mb-2">
                                    <i class="bi bi-house-door-fill me-1"></i> Estimated total price
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div><i class="bi bi-gavel"></i> Purchase amount</div>
                                    <div>$1,175</div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <div><i class="bi bi-box-seam"></i> Customs value</div>
                                    <div>$3,080</div>
                                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Final Price Calculator -->
                    <!-- <div class="card shadow-sm bitcx_amp_price_calc">
                        <div class="card-header">
                            <i class="bi bi-calculator-fill me-1"></i> Final Price Calculator
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-2">
                                <span class="badge bg-danger">Your bid</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>1 Lot Price</div>
                                <div class="fw-semibold">$1,175</div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>

        <?php if (!empty($related_vehicles)) : ?>
            <!-- Auction Grid -->
            <section class="bitcx_amp_auctions py-5">
                <div class="container">
                    <!-- Section Title -->
                    <div class="d-flex justify-content-center align-items-center mb-4">
                        <h2 class="bitcx_amp_section_title h4">Compare auctions</h2>
                    </div>
                    <!-- Cards -->
                    <div class="row bitcx_amp_row g-4">
                        <?php  
                            foreach ($related_vehicles as $vehicle): 
                                $vehicle_title = esc_html($vehicle->make . ' ' . $vehicle->model . ' ' . $vehicle->year);
                                $primary_image_url = $vehicle->primary_image_url ?? "https://placehold.co/845x633?text=Image+not+Available";
                                $auction_name = strtoupper($vehicle->auction_name);
                                
                                $remaining_str = AuctionMarketplace\Shortcodes::get_remaining_time($vehicle->sale_date);
                                $vehicle_url = AuctionMarketplace\Shortcodes::get_auction_link($vehicle->vin, $vehicle->auction_name, $primary_image_url, $vehicle->lot_number ?? null);
                                $sale_date_str = AuctionMarketplace\Shortcodes::format_sale_date($vehicle->sale_date);
                                
                                $auction_status = ($remaining_str != "Expired") ? $vehicle->status : 'inactive';
                                $classes = strtolower(esc_attr($vehicle->auction_name)) . ' ' . strtolower(esc_attr($auction_status));
                                $classes = trim($classes);
                        ?>
                                <div class="col-md-6 col-lg-3">
                                    <div class="card bitcx_amp_card h-100 <?php echo $classes; ?>">
                                        <a href="<?php echo esc_url($vehicle_url); ?>" class="bitcx_amp_card_link">
                                            <img src="<?php echo esc_url($primary_image_url); ?>" class="card-img-top bitcx_amp_card_img" alt="Car">
                                        </a>
                                        <div class="card-body bitcx_amp_card_body">
                                            <h5 class="bitcx_amp_card_title"><a href="<?php echo esc_url($vehicle_url); ?>" class="bitcx_amp_card_link"><?php echo esc_attr($vehicle_title); ?></a></h5>
                                            <p class="bitcx_amp_card_subtitle small text-muted">
                                                <span class="auction_name"><?php echo esc_attr($vehicle->auction_name) ?></span>
                                                <span class="status"><?php echo ucfirst(esc_attr($auction_status)); ?></span>
                                            </p>
                                            <div class="bitcx_amp_timer mb-2">
                                                <span class="bitcx_amp_time"><?php echo $remaining_str . (($remaining_str != "Expired") ? " (Time Left) " : " "); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between bid_price">
                                                <span class="bitcx_amp_price crnt_bid">Current Bid: <b><?php echo '$'.esc_attr($vehicle->crnt_bid_price ?? 0) ?></b></span>
                                                <?php if ($vehicle->buy_now): ?>
                                                    <span class="bitcx_amp_price buy_now">Buy Now: <b><?php echo '$'.esc_attr($vehicle->buy_now) ?></b></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        <?php endif; ?>

    <?php else : ?>
        <div class="container py-5 text-center">
            <h2 class="text-muted">No vehicle details available</h2>
            <p class="text-muted">Please check back later or contact support for assistance.</p>
        </div>

    <?php endif; ?>

</section>
