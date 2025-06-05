<!-- listing-container  -->
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-white rounded border">
        
        <!-- Active Filter Chip -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge rounded-pill bg-light text-dark px-3 py-2 d-flex align-items-center">
                0 - 250 000 miles
                <button type="button" class="btn-close btn-close-sm ms-2" aria-label="Remove filter"></button>
            </span>
        </div>

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

<div class="container py-4">
    <div class="row">
        <!-- Left Filter Sidebar -->
        <div class="col-md-12 col-lg-3">
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
                    <div class="mb-4">
                        <label class="fw-semibold">Auction Type</label>
                        <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-light rounded-pill px-3 bitcx_amp_filter_tab active">All</button>
                        <button class="btn btn-primary rounded-pill px-3 bitcx_amp_filter_tab">Copart</button>
                        <button class="btn btn-danger rounded-pill px-3 bitcx_amp_filter_tab">IAAI</button>
                        </div>
                    </div>

                    <!-- Start Code -->
                    <div class="mb-4">
                        <h6>Start code</h6>
                        <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm bitcx_amp_filter_tab">All</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Stationary / No information</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Vehicle starts</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Run and Drive</button>
                        </div>
                    </div>

                    <!-- Drive Type -->
                    <div class="mb-4">
                        <h6>Drive Type</h6>
                        <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm bitcx_amp_filter_tab">All</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">FWD Front wheel drive</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">RWD Rear wheel drive</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">AWD All wheel drive</button>
                        </div>
                    </div>

                    <!-- Transmission -->
                    <div class="mb-4">
                        <h6>Transmission</h6>
                        <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm bitcx_amp_filter_tab">All</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">A Automatic</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">M Manual</button>
                        </div>
                    </div>

                    <!-- Body Style -->
                    <div class="mb-4">
                        <h6>Body Style</h6>
                        <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm bitcx_amp_filter_tab active">All</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Sedan</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">SUV</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Coupe</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">Pickup</button>
                        <button class="btn btn-sm bitcx_amp_filter_tab">See more</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Listing Content -->
        <div class="col-md-12 col-lg-9">

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3 bitcx_amp_filter_tabs">
                <li class="nav-item">
                <a class="nav-link active" href="#">All</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="#">Opened Auction</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="#">Live</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="#">Finished Today</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="#">Fast Buy</a>
                </li>
            </ul>

            <!-- Card Listing -->
            <div class="card bitcx_amp_car_card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div id="carCarousel1" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                <div class="carousel-item active">
                                <img src="/assets/listing-img1.jpg" class="d-block w-100" alt="Car Image 1">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img2.jpg" class="d-block w-100" alt="Car Image 2">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img3.jpg" class="d-block w-100" alt="Car Image 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carCarousel1" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carCarousel1" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h5><a href="#" class="text-decoration-none ">2019 Mercedes-Benz C-Class, 300</a></h5>
                        <p class="text-muted small mb-1">• 55SWF8DB1KU296703 • 1-76256964</p>
                        <div class="d-flex gap-2 align-items-center mb-2">
                        <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                        <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                        </div>
                        <div class="row text-muted small">
                        <div class="col-6">Milage: <span class="text-dark">45k miles (73k km)</span></div>
                        <div class="col-6">Seller: <span class="text-dark">Non-insurance Company</span></div>
                        <div class="col-6">Location: <span class="text-dark">NCS MOUNTA...</span></div>
                        <div class="col-6">Damage: <span class="text-dark">Front end</span></div>
                        <div class="col-6">Sale doc.: <span class="text-dark">CT (Texas)</span></div>
                        <div class="col-6">Status: <span class="text-warning fw-medium">No information</span></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                        <div>
                            <button class="btn btn-success btn-sm mb-2">Copart</button>
                            <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="small text-muted mb-1">$4,950 - $5,500</div>
                        <div class="small mb-1"><i class="bi bi-calendar3"></i> Wed 4 Jun, 13:00 GMT+2</div>
                        <div class="small text-success mb-3"><i class="bi bi-clock"></i> 0 d 3 h 48 min left</div>
                        <div class="bg-light rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">$4,950</span>
                            <span class=" fw-bold bid-price">$7,500</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Current Bid:</span>
                            <span>Buy Now:</span>
                        </div>
                        </div>
                        <button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>
                    </div>
                </div>
            </div>

            <!-- Card Listing -->
            <div class="card bitcx_amp_car_card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div id="carCarousel1" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                <div class="carousel-item active">
                                <img src="/assets/listing-img1.jpg" class="d-block w-100" alt="Car Image 1">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img2.jpg" class="d-block w-100" alt="Car Image 2">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img3.jpg" class="d-block w-100" alt="Car Image 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carCarousel1" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carCarousel1" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h5><a href="#" class="text-decoration-none ">2019 Mercedes-Benz C-Class, 300</a></h5>
                        <p class="text-muted small mb-1">• 55SWF8DB1KU296703 • 1-76256964</p>
                        <div class="d-flex gap-2 align-items-center mb-2">
                        <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                        <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                        </div>
                        <div class="row text-muted small">
                        <div class="col-6">Milage: <span class="text-dark">45k miles (73k km)</span></div>
                        <div class="col-6">Seller: <span class="text-dark">Non-insurance Company</span></div>
                        <div class="col-6">Location: <span class="text-dark">NCS MOUNTA...</span></div>
                        <div class="col-6">Damage: <span class="text-dark">Front end</span></div>
                        <div class="col-6">Sale doc.: <span class="text-dark">CT (Texas)</span></div>
                        <div class="col-6">Status: <span class="text-warning fw-medium">No information</span></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                        <div>
                        <button class="btn btn-success btn-sm mb-2">Copart</button>
                        <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="small text-muted mb-1">$4,950 - $5,500</div>
                        <div class="small mb-1"><i class="bi bi-calendar3"></i> Wed 4 Jun, 13:00 GMT+2</div>
                        <div class="small text-success mb-3"><i class="bi bi-clock"></i> 0 d 3 h 48 min left</div>
                        <div class="bg-light rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">$4,950</span>
                            <span class=" fw-bold bid-price">$7,500</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Current Bid:</span>
                            <span>Buy Now:</span>
                        </div>
                        </div>
                        <button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>
                    </div>
                </div>
            </div>

            <!-- Card Listing -->
            <div class="card bitcx_amp_car_card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div id="carCarousel1" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                <div class="carousel-item active">
                                <img src="/assets/listing-img1.jpg" class="d-block w-100" alt="Car Image 1">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img2.jpg" class="d-block w-100" alt="Car Image 2">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img3.jpg" class="d-block w-100" alt="Car Image 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carCarousel1" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carCarousel1" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h5><a href="#" class="text-decoration-none ">2019 Mercedes-Benz C-Class, 300</a></h5>
                        <p class="text-muted small mb-1">• 55SWF8DB1KU296703 • 1-76256964</p>
                        <div class="d-flex gap-2 align-items-center mb-2">
                        <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                        <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                        </div>
                        <div class="row text-muted small">
                        <div class="col-6">Milage: <span class="text-dark">45k miles (73k km)</span></div>
                        <div class="col-6">Seller: <span class="text-dark">Non-insurance Company</span></div>
                        <div class="col-6">Location: <span class="text-dark">NCS MOUNTA...</span></div>
                        <div class="col-6">Damage: <span class="text-dark">Front end</span></div>
                        <div class="col-6">Sale doc.: <span class="text-dark">CT (Texas)</span></div>
                        <div class="col-6">Status: <span class="text-warning fw-medium">No information</span></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                        <div>
                            <button class="btn btn-success btn-sm mb-2">Copart</button>
                            <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="small text-muted mb-1">$4,950 - $5,500</div>
                        <div class="small mb-1"><i class="bi bi-calendar3"></i> Wed 4 Jun, 13:00 GMT+2</div>
                        <div class="small text-success mb-3"><i class="bi bi-clock"></i> 0 d 3 h 48 min left</div>
                        <div class="bg-light rounded p-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">$4,950</span>
                                <span class=" fw-bold bid-price">$7,500</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Current Bid:</span>
                                <span>Buy Now:</span>
                            </div>
                        </div>
                        <button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>
                    </div>
                </div>
            </div>

            <!-- Card Listing -->
            <div class="card bitcx_amp_car_card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div id="carCarousel1" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                <div class="carousel-item active">
                                <img src="/assets/listing-img1.jpg" class="d-block w-100" alt="Car Image 1">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img2.jpg" class="d-block w-100" alt="Car Image 2">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img3.jpg" class="d-block w-100" alt="Car Image 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carCarousel1" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carCarousel1" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h5><a href="#" class="text-decoration-none ">2019 Mercedes-Benz C-Class, 300</a></h5>
                        <p class="text-muted small mb-1">• 55SWF8DB1KU296703 • 1-76256964</p>
                        <div class="d-flex gap-2 align-items-center mb-2">
                        <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                        <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                        </div>
                        <div class="row text-muted small">
                        <div class="col-6">Milage: <span class="text-dark">45k miles (73k km)</span></div>
                        <div class="col-6">Seller: <span class="text-dark">Non-insurance Company</span></div>
                        <div class="col-6">Location: <span class="text-dark">NCS MOUNTA...</span></div>
                        <div class="col-6">Damage: <span class="text-dark">Front end</span></div>
                        <div class="col-6">Sale doc.: <span class="text-dark">CT (Texas)</span></div>
                        <div class="col-6">Status: <span class="text-warning fw-medium">No information</span></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                        <div>
                        <button class="btn btn-success btn-sm mb-2">Copart</button>
                        <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="small text-muted mb-1">$4,950 - $5,500</div>
                        <div class="small mb-1"><i class="bi bi-calendar3"></i> Wed 4 Jun, 13:00 GMT+2</div>
                        <div class="small text-success mb-3"><i class="bi bi-clock"></i> 0 d 3 h 48 min left</div>
                        <div class="bg-light rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">$4,950</span>
                            <span class=" fw-bold bid-price">$7,500</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Current Bid:</span>
                            <span>Buy Now:</span>
                        </div>
                        </div>
                        <button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>
                    </div>
                </div>
            </div>
            
            <!-- Card Listing -->
            <div class="card bitcx_amp_car_card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div id="carCarousel1" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                <div class="carousel-item active">
                                <img src="/assets/listing-img1.jpg" class="d-block w-100" alt="Car Image 1">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img2.jpg" class="d-block w-100" alt="Car Image 2">
                                </div>
                                <div class="carousel-item">
                                <img src="/assets/listing-img3.jpg" class="d-block w-100" alt="Car Image 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carCarousel1" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carCarousel1" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h5><a href="#" class="text-decoration-none ">2019 Mercedes-Benz C-Class, 300</a></h5>
                        <p class="text-muted small mb-1">• 55SWF8DB1KU296703 • 1-76256964</p>
                        <div class="d-flex gap-2 align-items-center mb-2">
                        <span class="badge bg-light text-secondary border"><i class="bi bi-key"></i></span>
                        <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text"></i></span>
                        </div>
                        <div class="row text-muted small">
                        <div class="col-6">Milage: <span class="text-dark">45k miles (73k km)</span></div>
                        <div class="col-6">Seller: <span class="text-dark">Non-insurance Company</span></div>
                        <div class="col-6">Location: <span class="text-dark">NCS MOUNTA...</span></div>
                        <div class="col-6">Damage: <span class="text-dark">Front end</span></div>
                        <div class="col-6">Sale doc.: <span class="text-dark">CT (Texas)</span></div>
                        <div class="col-6">Status: <span class="text-warning fw-medium">No information</span></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end d-flex flex-column justify-content-between">
                        <div>
                        <button class="btn btn-success btn-sm mb-2">Copart</button>
                        <button class="btn btn-light btn-sm mb-2"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="small text-muted mb-1">$4,950 - $5,500</div>
                        <div class="small mb-1"><i class="bi bi-calendar3"></i> Wed 4 Jun, 13:00 GMT+2</div>
                        <div class="small text-success mb-3"><i class="bi bi-clock"></i> 0 d 3 h 48 min left</div>
                        <div class="bg-light rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">$4,950</span>
                            <span class=" fw-bold bid-price">$7,500</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Current Bid:</span>
                            <span>Buy Now:</span>
                        </div>
                        </div>
                        <button class="btn btn-success rounded-pill w-100 theme-btn">Opened auction</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>