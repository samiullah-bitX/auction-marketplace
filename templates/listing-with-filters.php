<!-- listing-container  -->
<div class="container mt-3 px-0">
    <div class="d-flex tags-manager justify-content-between align-items-center px-3 py-2 bg-white rounded border">
        
        <!-- Active Filter Chip -->
        <?php if (!empty($listing_params)): ?>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php foreach ($listing_params as $key => $value): ?>
                    <span class="badge rounded-pill  text-dark px-3 py-2 d-flex align-items-center">
                        <?php
                            $label = str_replace(['-', '_'], ' ', $key);
                            $label = ucwords($label);
                            if (in_array(strtolower($key), ['copart', 'iaai', 'archive'])) {
                                echo htmlspecialchars(strtoupper($label));
                            } else {
                                echo htmlspecialchars($label) . " : " . htmlspecialchars($value);
                            }
                        ?>
                        <button type="button" class="btn-close btn-close-sm ms-2" aria-label="Remove filter"></button>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Sort By -->
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-ul text-muted"></i>
            <label for="sortSelect" class="mb-0 text-muted">Sort By</label>
            <select id="sortSelect" class="form-select form-select-sm bitcx_amp_filter_input" style="width: auto;">
                <option selected>A - Z</option>
                <option>Cars Auction (85)</option>
                <option>Z - A</option>
                <option>Newest First</option>
            </select>
        </div>

    </div>
</div>

<div class="container py-4 px-0">
    <div class="row">
        <!-- Left Filter Sidebar -->
        <div class="col-md-12 col-lg-3 pe-lg-0">
            <div class="card bitcx_amp_filter_box">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Filters</h5>

                    <!-- Estimated Price -->
                    <div class="mb-4">
                        <label class="fw-semibold">Estimated price ($)</label>
                        <div class="d-flex justify-content-between small">
                            <span></span>
                            <span class="text-muted">USD</span>
                        </div>
                        <input type="range" class="form-range" min="0" max="100000">
                        <div class="d-flex gap-2 mt-2">
                            <input type="number" class="form-control" value="0">
                            <span class="align-self-center">—</span>
                            <input type="number" class="form-control" value="100000">
                        </div>
                    </div>

                    <!-- Year -->
                    <div class="mb-4">
                        <label class="fw-semibold">Year</label>
                        <div class="d-flex gap-2">
                            <input type="number" class="form-control" value="1900">
                            <span class="align-self-center">—</span>
                            <input type="number" class="form-control" value="2026">
                        </div>
                    </div>

                    <!-- Auction Type -->
                    <div class="mb-4 auction-type">
                        <label class="fw-semibold">Auction Type</label>
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-light rounded-pill px-3 bitcx_amp_filter_tab active">All</button>
                            <button class="btn btn-primary rounded-pill px-3 bitcx_amp_filter_tab">Copart</button>
                            <button class="btn btn-danger rounded-pill px-3 bitcx_amp_filter_tab">IAAI</button>
                        </div>
                    </div>

                    <!-- Start Code -->
                    <div class="mb-4 filter-btns">
                        <h6 class="mb-1">Start code</h6>
                        <div class="d-flex flex-wrap gap-2 ">
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">All</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Stationary / No information</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Vehicle starts</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Run and Drive</button>
                        </div>
                    </div>

                    <!-- Drive Type -->
                    <div class="mb-4 filter-btns">
                        <h6 class="mb-1">Drive Type</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">All</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">FWD Front wheel drive</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">RWD Rear wheel drive</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">AWD All wheel drive</button>
                        </div>
                    </div>

                    <!-- Transmission -->
                    <div class="mb-4 filter-btns">
                        <h6 class="mb-1">Transmission</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">All</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">A Automatic</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">M Manual</button>
                        </div>
                    </div>

                    <!-- Body Style -->
                    <div class="mb-4 filter-btns">
                        <h6 class="mb-1">Body Style</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab active">All</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Sedan</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">SUV</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Coupe</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">Pickup</button>
                            <button class="btn btn-sm rounded-pill bitcx_amp_filter_tab">See more</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Listing Content -->
        <div class="col-md-12 col-lg-9">
            <div id="listing-results">
                <?php if (!empty($results)): ?>
                    <div class="mb-3">
                        <span class="fw-semibold"><?php echo esc_html(count($results)); ?></span> results found
                    </div>
                <?php endif; ?>
                <?php if (!empty($results)): ?>
                    <!-- Card Listing -->
                        <?php 
                            // error_log("Results: " . print_r($results, true));
                            foreach ($results as $key => $car) { 
                                $car_title = esc_html($car->make . ' ' . $car->model . ' ' . $car->year);
                                $primary_image_url = $car->primary_image_url ?? "https://placehold.co/845x633?text=Image+not+Available";
                                $auction_name = strtoupper($car->auction_name);
                                
                                $images = AuctionMarketplace\Shortcodes::get_car_images($car, $primary_image_url);
                                $remaining_str = AuctionMarketplace\Shortcodes::get_remaining_time($car->sale_date);
                                $sale_date_str = AuctionMarketplace\Shortcodes::format_sale_date($car->sale_date);
                                $vehicle_url = AuctionMarketplace\Shortcodes::get_auction_link($car->vin, $car->auction_name, $primary_image_url, $car->lot_number ?? null);
                                
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
                            <div class="card bitcx_amp_car_card vehicle-details-carousel  p-3 mb-4 <?php echo $classes; ?>">
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
                                    <div class="col-md-5">
                                        <h5 class="d-flex align-items-center">
                                            <a href="<?php echo esc_url($vehicle_url); ?>"><?php echo esc_attr($car_title); ?></a>
                                            <p class="text-muted small mb-1">• <?php echo esc_attr($car->vin); ?> • <?php echo esc_attr($car->lot_number); ?></p>

                                        </h5>
                                        <div class="extra-information"><div class="specs"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Key Present"><img src="https://bid.cars/img/upd/icons/key.svg" width="20" height="20" alt="Key Present"></span><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Automatic"><img src="https://bid.cars/img/upd/icons/automatics.svg" width="18" height="19" alt="Automatic"></span><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Gasoline"><img src="https://bid.cars/img/upd/icons/patrol.svg" width="15" height="18" alt="Gasoline"></span><span class="drive-type" data-toggle="tooltip" data-placement="top" title="" data-original-title="Front wheel drive"><img src="https://bid.cars/img/upd/icons/fwd.svg" width="15" height="18" alt="Front wheel drive"></span><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Engine size, type, horsepower">1.5L</span><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Engine size, type, horsepower">I4</span><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Engine size, type, horsepower">200HP</span></div></div>
                                        <!-- <div class="d-flex gap-2 align-items-center mb-2">
                                            <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                                            <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                                        </div> -->
                                        <div class="row text-muted small item-info">
                                            <div class="col-6 vstack">Odometer [km]: <span class="text-dark"><?php echo esc_attr($odometer_value ?? "N/A"); ?></span></div>
                                            <div class="col-6 vstack">Seller: <span class="text-dark"><?php echo ($car->seller ? esc_attr($car->seller) : "N/A"); ?></span></div>
                                            <div class="col-6 vstack">Location: <span class="text-dark"><?php echo esc_attr($car->location); ?></span></div>
                                            <div class="col-6 vstack">Damage: <span class="text-dark"><?php echo esc_attr($car->primary_damage); ?></span></div>
                                            <div class="col-6 vstack">Sale doc: <span class="text-dark"><?php echo esc_attr(($car->raw_json != NULL) ? $car->raw_json["doc_type"] : "N/A") ?></span></div>
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
                <?php
                    echo '<div class="alert alert-info">No Results Found, Try broaden your search.</div>';
                    endif;
                ?>
            </div>

            <!-- AJAX-inserted page buttons -->
            <div id="pagination-controls" class="d-flex gap-2 justify-content-end pagination"></div>
        </div>
    </div>
</div>