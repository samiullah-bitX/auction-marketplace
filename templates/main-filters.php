<section class="bitcx_amp_filter_section py-4 border-bottom">
    <div class="container">
      <div class="bg-light" style="padding: 40px; border-radius: 12px;">
        <!-- Top Tabs Row -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="bitcx_amp_archived">
            <label class="form-check-label text-muted" for="bitcx_amp_archived">Archived</label>
          </div>
          <div class="d-flex gap-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="copart" checked="">
              <label class="form-check-label text-primary fw-bold" for="copart">Copart</label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="iaai" checked="">
              <label class="form-check-label text-danger fw-bold" for="iaai">IAAI</label>
            </div>
          </div>
        </div>

      <!-- Form Section -->
      <div class="row g-3 align-items-center">
        <!-- Column 1 -->
        <div class="col-md-4">
          <div class="form-group mb-3">
            <select class="form-select bitcx_amp_filter_input mb-3 select2-drop" id="vehicle_make" name="vehicle_make">
              <option value="">All makes</option>
              <?php 
                  if (!empty($vehicle_makes)) {
                      foreach ($vehicle_makes as $key => $make): ?>
                          <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($make); ?></option>
              <?php 
                      endforeach; 
                  }
              ?>
            </select>
          </div>

          <div class="form-group">
            <select class="form-select bitcx_amp_filter_input select2-drop" name="year_range" id="year_range">
                <option value="">Изберете Годишен диапазон</option>
                <?php
                    $start_year = 1990;
                    $future_year = date('Y');
                    for($year = $future_year; $year >= $start_year; $year -= 5) {
                        $range_end = $year;
                        $range_start = max($year - 4, $start_year);
                        if ($range_start === $range_end) {
                            continue;
                        }
                        $value = $range_start . '-' . $range_end;
                        $display = $range_start . ' - ' . $range_end;
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($display) . '</option>';
                    }
                ?>
            </select>
          </div>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4">
          <div class="form-group mb-3">
            <select class="form-select bitcx_amp_filter_input mb-3 select2-drop" name="vehicle_model" id="vehicle_model" disabled>
              <option value="">All models</option>
            </select>
          </div>
          <div class="form-group">
            <select name="bid_range" id="bid_range" class="form-select bitcx_amp_filter_input select2-drop">
                <option value="">Изберете Ценови диапазон</option>
                <option value="$0-$1000">$0 - $1,000</option>
                <option value="$1000-$5000">$1,000 - $5,000</option>
                <option value="$5000-$10000">$5,000 - $10,000</option>
                <option value="$10000-$15000">$10,000 - $15,000</option>
                <option value="$15000-$25000">$15,000 - $25,000</option>
                <option value="$25000-$50000">$25,000 - $50,000</option>
                <option value="$50000-$100000">$50,000 - $100,000</option>
                <option value="$100000-$200000">Над $100,000</option>
            </select>
          </div>
        </div>

        <!-- Column 3 -->
        <div class="col-md-4">
          <div class="d-flex align-items-center mb-3">
            <span class="me-2 fw-semibold text-muted">or</span>
            <input type="text" class="form-control bitcx_amp_filter_input" placeholder="Search by VIN or lot number">
          </div>
          <div class="d-flex justify-content-between align-items-center ms-4">
            <button type="submit" class="btn btn-primary px-4 w-100 main-filters-btn">Search vehicles</button>
          </div>
        </div>
      </div>
      </div>
    </div>
</section>