<?php if (!empty($cars)): ?>
    <?php if (isset($initial_load) && $initial_load): ?>
        <div class="mb-3">
            <span class="fw-semibold"><?php echo esc_html($total); ?></span> results found
        </div>
    <?php endif; ?>
    <!-- Card Listing -->
        <?php 
            // error_log("Results: " . print_r($results, true));
            foreach ($cars as $key => $car) { 
                $car_title = esc_html($car->make . ' ' . $car->model . ' ' . $car->year);
                $primary_image_url = $car->primary_image_url ?? "https://placehold.co/845x633?text=Image+not+Available";
                $auction_name = strtoupper($car->auction_name);
                
                $remaining_str = AuctionMarketplace\Shortcodes::get_remaining_time($car->sale_date);
                $vehicle_url = AuctionMarketplace\Shortcodes::get_auction_link($car->vin, $car->auction_name, $primary_image_url, $car->lot_number ?? null);
                $sale_date_str = AuctionMarketplace\Shortcodes::format_sale_date($car->sale_date);
                $images = AuctionMarketplace\Shortcodes::get_car_images($car, $primary_image_url);
                
                $drive_icon = AuctionMarketplace\Shortcodes::get_drive_icon($car->drive);
                $tranmission_icon = AuctionMarketplace\Shortcodes::get_tranmission_icon($car->transmission);
                $key_icon = AuctionMarketplace\Shortcodes::get_key_icon($car->raw_json["car_keys"]);
                $fuel_icon = AuctionMarketplace\Shortcodes::get_fuel_icon($car->fuel);

                $engine_type = $car->raw_json["engine_type"] ?? "N/A";
                $engine_hp = $car->engine_json["result"]["engine"]["engine_brake_hp_from"] ?? "N/A";
                
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
                <div class="card bitcx_amp_car_card p-3 mb-4 <?php echo $classes; ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div id="<?php echo esc_attr($carousel_id); ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner rounded">
                                    <?php foreach ($images as $idx => $img_url): ?>
                                        <div class="carousel-item<?php echo $idx === 0 ? ' active' : ''; ?>">
                                            <img src="<?php echo esc_url($img_url); ?>" class="d-block w-100" alt="Car Image <?php echo $idx + 1; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($images) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-5 item-info">
                            <h5 class="d-flex align-items-center">
                                <a href="<?php echo esc_url($vehicle_url); ?>"><?php echo esc_attr($car_title); ?></a>
                                <p class="text-muted small mb-1">• <?php echo esc_attr($car->vin); ?> • <?php echo esc_attr($car->lot_number); ?></p>
                            </h5>
                            <div class="extra-information">
                                <div class="specs">
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Key Present">
                                        <!-- <img src="https://bid.cars/img/upd/icons/key.svg" width="20" height="20" alt="Key Present"> -->
                                        <img src="<?php echo plugins_url('assets/images/'.$key_icon, dirname(__FILE__, 2)); ?>" width="20" height="20" alt="Key Present">
                                    </span>
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Transmission">
                                        <!-- <img src="https://bid.cars/img/upd/icons/automatics.svg" width="18" height="19" alt="Automatic"> -->
                                        <img src="<?php echo plugins_url('assets/images/'.$tranmission_icon, dirname(__FILE__, 2)); ?>" width="20" height="20" alt="Transmission">
                                    </span>
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Fuel Type">
                                        <!-- <img src="https://bid.cars/img/upd/icons/patrol.svg" width="15" height="18" alt="Gasoline"> -->
                                        <img src="<?php echo plugins_url('assets/images/'.$fuel_icon, dirname(__FILE__, 2)); ?>" width="20" height="20" alt="Drive Type">
                                    </span>
                                    <span class="drive-type" data-toggle="tooltip" data-placement="top" title="" data-original-title="Drive Type">
                                        <!-- <img src="https://bid.cars/img/upd/icons/fwd.svg" width="15" height="18" alt="Front wheel drive"> -->
                                        <img src="<?php echo plugins_url('assets/images/'.$drive_icon, dirname(__FILE__, 2)); ?>" width="20" height="20" alt="Drive Type">
                                    </span>
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Engine size, Type">
                                        <?php echo esc_attr($engine_type); ?>
                                    </span>
                                    <!-- <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Engine size, type, horsepower">
                                        I4
                                    </span> -->
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Horsepower">
                                        <?php echo esc_attr($engine_hp).($engine_hp != "N/A" ? "HP" : ""); ?>
                                    </span>
                                </div>
                            </div>
                            <!-- <div class="d-flex gap-2 align-items-center mb-2">
                                <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                                <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                            </div> -->
                            <div class="row text-muted small">
                                <div class="col-6 vstack">Odometer [km]: <span class="text-dark"><?php echo esc_attr($odometer_value ?? "N/A"); ?></span></div>
                                <div class="col-6 vstack">Seller: <span class="text-dark"><?php echo ($car->seller ? esc_attr($car->seller) : "N/A"); ?></span></div>
                                <div class="col-6 vstack">Location: <span class="text-dark"><?php echo esc_attr($car->location); ?></span></div>
                                <div class="col-6 vstack">Damage: <span class="text-dark"><?php echo esc_attr($car->primary_damage); ?></span></div>
                                <div class="col-6 vstack">Sale doc: <span class="text-dark"><?php echo esc_attr($car->raw_json["doc_type"] ?? "N/A") ?></span></div>
                                <div class="col-6 vstack">Status: <span class="text-warning fw-medium"><?php echo esc_attr($auction_status); ?></span></div>
                            </div>
                        </div>
                        <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                            <div>
                                <button class="btn <?php echo ($isCopart ? "btn-primary" : "btn-danger"); ?> btn-sm mb-2"><?php echo esc_attr($auction_name); ?></button>
                                <!-- <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button> -->
                            </div>
                            <!-- <div class="small text-muted mb-1">$4,950 - $5,500</div> -->
                            <div class="small mb-1">
                                <i class="bi bi-calendar3"></i>
                                <span class="<?php echo esc_attr($auction_status == "inactive" ? "text-danger" : "text-success"); ?>">
                                    <?php echo esc_html($sale_date_str); ?>
                                </span>
                            </div>
                            <div class="small <?php echo esc_attr ($auction_status == "inactive") ? "hide" : "text-success" ; ?> mb-3"><i class="bi bi-clock"></i> <span class="bitcx_amp_time"><?php echo $remaining_str . (($remaining_str != "Expired") ? " (Time Left) " : " "); ?> </span></div>
                            <div class="bg-light rounded p-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold"><?php echo '$'.esc_attr($car->crnt_bid_price ?? 0) ?></span>
                                    <?php if ($car->buy_now): ?>
                                        <span class="fw-bold bid-price"><?php echo '$'.esc_attr($car->buy_now) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>Current Bid:</span>
                                    <?php if ($car->buy_now): ?>
                                        <span>Buy Now:</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                                if ($auction_status != "inactive") :
                                    echo '<button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>';
                                else:
                                    echo '<button class="btn btn-danger rounded-pill w-100 theme-btn">Expired</button>';
                                endif;
                            ?>
                        </div>
                    </div>
                </div>
        <?php } ?>
    </div>

<?php else: ?>
    <div class="alert alert-info">No Results Found, Try broaden your search.</div>
<?php endif; ?>