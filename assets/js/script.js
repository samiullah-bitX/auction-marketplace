jQuery(document).ready(function($) {
    const phoneValidInput = document.getElementById('phone-valid');
    const validationMessage = $('#phone-validation-message');
    const phoneInput = $('#user_phone');
    
    let ajaxURL = carAuctionAjax.ajaxurl;

    // Handle form submission
    $('#lead-form').on('submit', function(e) {
        e.preventDefault();

        let isValid = true;
        let formData = {};

        // Validate required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('field-error'); // Add error class for styling
            } else {
                $(this).removeClass('field-error'); // Remove error class if valid
            }
        });

        if (!isValid) {
            simpleToast('error', 'Моля, попълнете всички задължителни полета.');
            // $('#form-message').html('<div class="error-message">Please fill all required fields.</div>');
            return;
        }

        if (phoneValidInput.value === '0') {
            simpleToast('error', 'въведете валиден телефонен номер');
            return;
        }

        $("#auctionPreloader").show(); // Show preloader

        // Collect form data
        $(this).find(':input', 'select').each(function() {
            const name = $(this).attr('name');
            const type = $(this).attr('type');
            if (name) {
                if (type === 'checkbox') {
                    formData[name] = $(this).is(':checked') ? $(this).val() : 0;
                } else if (name === 'auction_names') {
                    const selectedValues = $(this).val();
                    formData['auction_name'] = selectedValues[0] || '';
                    formData['auction_names'] = selectedValues.slice(1) || [];
                } else if (name === 'vehicle_make'|| name === 'vehicle_model') {
                    formData[name] = $(this).find('option:selected').text();
                } else {
                    formData[name] = $(this).val();
                }
            }
        });

        // console.log('Form Data:', formData);

        // Send AJAX request
        $.ajax({
            url: ajaxURL,
            type: 'POST',
            data: {
                action: 'get_vehicle_active_lots',
                form_data: formData
            },
            success: function(response) {
                $("#auctionPreloader").hide(); // Show preloader
                if (response.success) {
                    simpleToast('success', 'Вашето запитване беше изпратено успешно!');
                    const countryCode = $('#country_code').val();
                    $('#lead-form')[0].reset();
                    $('.select2-drop').val(null).trigger('change');
                    $('#country_code').val(countryCode).trigger('change');
                    validationMessage.text('');
                    phoneValidInput.value = '0';
                    // $('#form-message').html('<div class="success-message">Data submitted successfully!</div>');
                } else {
                    console.error('Error in response:', response.data);
                    simpleToast('error', 'Грешка: ' + (response.data || 'Възникна грешка'));
                    // $('#form-message').html('<div class="error-message">Error: ' + (response.data || 'An error occurred') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $("#auctionPreloader").hide();
                simpleToast('error', 'Грешка: Нещо се обърка');
                console.log('AJAX Error:', error);
                // $('#form-message').html('<div class="error-message">An error occurred: ' + error + '</div>');
            }
        });
    });

    jQuery('#vehicle_make').on('change', function() {
        const selectedMakeID = jQuery(this).val();
        
        if (!selectedMakeID) {
            return; // No Vehicle Make selected, exit the function
        }

        $("#auctionPreloader").show(); // Show preloader
        
        jQuery.ajax({
            url: ajaxURL,
            type: 'POST',
            data: {
                action: 'get_vehicle_model_by_make',
                vehicle_make_id: selectedMakeID
            },
            success: function(response) {
                $("#auctionPreloader").hide();
                if (response.success) {
                    const modelSelect = jQuery('#vehicle_model');
                    modelSelect.empty();
                    modelSelect.prop("disabled", false);
                    modelSelect.append('<option value="">Изберете Модел</option>');
                    
                    Object.entries(response.data).forEach(([id, name]) => {
                        modelSelect.append(`<option value="${id}">${name}</option>`);
                    });
                }else{
                    console.error('Error in response:', response.data);
                    simpleToast('error', 'Error:' + (response.data || 'fetching vehicle models'));
                }
            },error(xhr, status, error) {
                $("#auctionPreloader").hide();
                simpleToast('error', 'Грешка при зареждането на моделите на превозните средства');
                console.error('Грешка при зареждането на моделите на превозните средства:', error);
            }
        });

    });

    // Initialize Select2 for dropdowns
    $('.select2-drop').select2();

    $('.select2-drop.multiselect').select2({
        placeholder: 'Select Auction',
        multiple: true,
        allowClear: true
    }).val(null).trigger('change');

    // Initialize Phone Library for phone number validation

    phoneInput.on('input', function() {
        const selectedCountry = $('#country_code').val();
        if (!selectedCountry) {
            validationMessage.text('Моля, първо изберете код на държава').css({'color': 'red', 'font-weight': '600'});
            phoneValidInput.value = '0';
            return;
        }

        if (!$(this).val()) {
            validationMessage.text('');
            phoneValidInput.value = '0';
            return;
        }

        try {
            const phoneNumber = libphonenumber.parsePhoneNumber($(this).val(), selectedCountry);
            const europeanCountries = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB'];
            
            if (phoneNumber && phoneNumber.isValid() && europeanCountries.includes(phoneNumber.country)) {
                validationMessage.text('✓ Валиден телефонен номер').css({'color': 'green', 'font-weight': '600'});
                phoneValidInput.value = '1';
                $(this).val(phoneNumber.format('INTERNATIONAL'));
            } else {
                validationMessage.text('✗ Невалиден телефонен номер').css({'color': 'red', 'font-weight': '600'});
                phoneValidInput.value = '0';
            }
        } catch (error) {
            validationMessage.text('✗ Невалиден телефонен номер').css({'color': 'red', 'font-weight': '600'});
            phoneValidInput.value = '0';
        }
    });

    function simpleToast(status = "success", msg = '') {
        jQuery("#auctionSimpleToast").text(msg);
        var x = document.getElementById("auctionSimpleToast");
        let classes = `show ${status}`;
        x.className = `show ${status}`;
        setTimeout(function(){ x.className = x.className.replace(classes, ""); }, 3000);
    }

    function loadPage(page, filters) {
        $.post(ajaxURL, {
            action: 'paginate_auctions',
            filters: filters,
            page: page,
            _ajax_nonce: carAuctionAjax.pagination_nonce
        }, function(response) {
            if (response.success) {
                $('#listing-results').html(response.data.html);
                updatePagination(response.data.total_pages, response.data.current_page);
            }
        });
    }

    function updatePagination(totalPages, currentPage) {
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            const active = (i === currentPage) ? 'active' : '';
            html += `<button class="page-btn ${active}" data-page="${i}">${i}</button>`;
        }
        $('#pagination-controls').html(html);
    }

    // Add event listener for country code changes
    $('#country_code').on('change', function() {
        phoneInput.val('');
        validationMessage.text('');
        phoneValidInput.value = '0';
    });

    jQuery('.main-filters-btn').on('click', function() {
        const queryParams = [];

        // Collect filter values
        const vinOrLot = jQuery('.bitcx_amp_filter_input[type="text"]').val();
        if (vinOrLot) {
            queryParams.push(`vin=${encodeURIComponent(vinOrLot)}`);
        }else{
            const vehicleMakeID = jQuery('#vehicle_make').val();
            if (vehicleMakeID) {
                const vehicleMake = jQuery('#vehicle_make option:selected').text().trim();
                queryParams.push(`make=${encodeURIComponent(vehicleMake)}`);
            }
    
            const vehicleModelID = jQuery('#vehicle_model').val();
            if (vehicleModelID) {
                const vehicleModel = jQuery('#vehicle_model option:selected').text().trim();
                queryParams.push(`model=${encodeURIComponent(vehicleModel)}`);
            }
    
            const yearRange = jQuery('#year_range').val();
            if (yearRange) {
                queryParams.push(`year_range=${encodeURIComponent(yearRange)}`);
            }
    
            const bidRange = jQuery('#bid_range').val();
            if (bidRange) {
                queryParams.push(`bid_range=${encodeURIComponent(bidRange)}`);
            }
    
            const archived = jQuery('#bitcx_amp_archived').is(':checked');
            if (archived) {
                queryParams.push(`archived=1`);
            }
    
            const copart = jQuery('#copart').is(':checked');
            if (copart) {
                queryParams.push(`copart=1`);
            }
    
            const iaai = jQuery('#iaai').is(':checked');
            if (iaai) {
                queryParams.push(`iaai=1`);
            }
        }

        // Redirect to auction-filters page with query params
        const queryString = queryParams.join('&');
        window.location.href = `auction-filters?${queryString}`;
    });

    $(document).on('click', '.page-btn', function() {
        const page = $(this).data('page');
        const filters = {};
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            filters[key] = value;
        });
        loadPage(page, filters);
    });

    // Only run pagination code on the auction-filters page
    if (window.location.pathname.includes('auction-filters')) {
        const initialFilters = {};
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            initialFilters[key] = value;
        });

        loadPage(1, initialFilters);
    }

    // Updated the JavaScript code to use a unique selector for the carousel to avoid affecting other carousels on different templates

    var specificCarousel = document.querySelector('.vehicle-details-carousel .carousel'); // Use a unique selector
    if (specificCarousel) {
        var thumbs = specificCarousel.parentNode.querySelectorAll('.bitcx_amp_thumb_img');
        var bsCarousel = bootstrap.Carousel.getOrCreateInstance(specificCarousel);
        specificCarousel.addEventListener('slide.bs.carousel', function (e) {
            thumbs.forEach(function(img, idx) {
                img.classList.toggle('active', idx === e.to);
            });
        });
        // Clicking a thumbnail also highlights it
        thumbs.forEach(function(img, idx) {
            img.addEventListener('click', function() {
                thumbs.forEach(function(i) { i.classList.remove('active'); });
                img.classList.add('active');
            });
        });
    }

    // Load More Button

    $('#load-more-btn').on('click', function() {
        let button = $(this);
        let offset = button.data('offset');
        const filters = {};
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            filters[key] = value;
        });
    
        $.ajax({
            url: ajaxURL,
            type: 'POST',
            data: {
                action: 'load_more_cars',
                offset: offset,
                filters: filters,
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    $('#listing-results').append(response.data.html);
                    button.data('offset', response.data.next_offset);
                    if (!response.data.has_more) {
                        button.remove(); // remove button if no more data
                    }
                } else {
                    button.text('No More Cars').prop('disabled', true);
                }
            }
        });
    });
      
    
});

    