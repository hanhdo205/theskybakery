<?php
/**
 * Pickup Scheduling System
 * 
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get available pickup dates for a store
 */
function tsb_get_available_pickup_dates($store_id, $num_days = 14, $custom_cake = false) {
    $dates = array();
    $min_notice = $custom_cake 
        ? get_theme_mod('tsb_custom_cake_notice', 3) 
        : get_theme_mod('tsb_min_notice_days', 1);
    
    $start_date = new DateTime('now', new DateTimeZone(wp_timezone_string()));
    $start_date->modify('+' . $min_notice . ' days');
    
    $hours = tsb_get_store_hours_array($store_id);
    
    for ($i = 0; $i < $num_days; $i++) {
        $check_date = clone $start_date;
        $check_date->modify('+' . $i . ' days');
        
        $day = strtolower($check_date->format('l'));
        
        // Check if store is open on this day
        if (isset($hours[$day]) && (!isset($hours[$day]['closed']) || !$hours[$day]['closed'])) {
            $dates[] = array(
                'date'      => $check_date->format('Y-m-d'),
                'display'   => $check_date->format('D, M j'),
                'day_name'  => $check_date->format('l'),
                'available' => true,
            );
        }
    }
    
    return $dates;
}

/**
 * Get available pickup time slots for a date
 */
function tsb_get_available_time_slots($store_id, $date) {
    $slots = array();
    $interval = get_theme_mod('tsb_time_slot_interval', 30);
    
    $hours = tsb_get_store_hours_array($store_id);
    $day = strtolower(date('l', strtotime($date)));
    
    if (!isset($hours[$day]) || (isset($hours[$day]['closed']) && $hours[$day]['closed'])) {
        return $slots;
    }
    
    $open_time = strtotime($date . ' ' . $hours[$day]['open']);
    $close_time = strtotime($date . ' ' . $hours[$day]['close']);
    
    // Allow pickup until 30 minutes before closing
    $last_pickup = $close_time - (30 * 60);
    
    // If it's today, start from the next available slot
    $now = current_time('timestamp');
    $today = date('Y-m-d', $now);
    
    if ($date === $today) {
        // Round up to next interval
        $current_minutes = date('i', $now);
        $round_up = $interval - ($current_minutes % $interval);
        $min_time = $now + ($round_up * 60) + (60 * 60); // Add 1 hour buffer
        $open_time = max($open_time, $min_time);
    }
    
    $current = $open_time;
    
    while ($current <= $last_pickup) {
        $slots[] = array(
            'time'    => date('H:i', $current),
            'display' => date('g:i A', $current),
        );
        $current += ($interval * 60);
    }
    
    return $slots;
}

/**
 * Validate pickup date and time
 */
function tsb_validate_pickup($store_id, $date, $time, $custom_cake = false) {
    $errors = array();
    
    // Check store exists
    $store = get_post($store_id);
    if (!$store || $store->post_type !== 'store_location') {
        $errors[] = __('Invalid pickup location selected.', 'theskybakery');
        return $errors;
    }
    
    // Check date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = __('Invalid date format.', 'theskybakery');
        return $errors;
    }
    
    // Check minimum notice
    $min_notice = $custom_cake 
        ? get_theme_mod('tsb_custom_cake_notice', 3) 
        : get_theme_mod('tsb_min_notice_days', 1);
    
    $pickup_date = new DateTime($date, new DateTimeZone(wp_timezone_string()));
    $min_date = new DateTime('now', new DateTimeZone(wp_timezone_string()));
    $min_date->modify('+' . $min_notice . ' days');
    $min_date->setTime(0, 0, 0);
    
    if ($pickup_date < $min_date) {
        $errors[] = sprintf(
            __('Pickup date must be at least %d day(s) from today.', 'theskybakery'),
            $min_notice
        );
    }
    
    // Check store is open on that day
    $hours = tsb_get_store_hours_array($store_id);
    $day = strtolower($pickup_date->format('l'));
    
    if (!isset($hours[$day]) || (isset($hours[$day]['closed']) && $hours[$day]['closed'])) {
        $errors[] = sprintf(
            __('The selected store is closed on %s.', 'theskybakery'),
            $pickup_date->format('l')
        );
    }
    
    // Check time is valid
    if (!empty($time)) {
        $available_slots = tsb_get_available_time_slots($store_id, $date);
        $valid_time = false;
        
        foreach ($available_slots as $slot) {
            if ($slot['time'] === $time) {
                $valid_time = true;
                break;
            }
        }
        
        if (!$valid_time && !empty($available_slots)) {
            $errors[] = __('The selected pickup time is not available.', 'theskybakery');
        }
    }
    
    return $errors;
}

/**
 * Format pickup details for display
 */
function tsb_format_pickup_details($store_id, $date, $time) {
    $store = get_post($store_id);
    
    if (!$store) {
        return '';
    }
    
    $store_name = $store->post_title;
    $store_address = get_post_meta($store_id, '_tsb_store_address', true);
    
    $formatted_date = date('l, F j, Y', strtotime($date));
    $formatted_time = date('g:i A', strtotime($time));
    
    return sprintf(
        '<div class="pickup-details">
            <p><strong>%s</strong></p>
            <p>%s</p>
            <p><i class="far fa-calendar"></i> %s</p>
            <p><i class="far fa-clock"></i> %s</p>
        </div>',
        esc_html($store_name),
        esc_html($store_address),
        esc_html($formatted_date),
        esc_html($formatted_time)
    );
}

/**
 * Pickup scheduling shortcode
 */
function tsb_pickup_scheduler_shortcode($atts) {
    $atts = shortcode_atts(array(
        'store_id'    => '',
        'custom_cake' => 'false',
    ), $atts, 'pickup_scheduler');
    
    $stores = tsb_get_stores();
    
    if (empty($stores)) {
        return '<p>No pickup locations available.</p>';
    }
    
    $custom_cake = $atts['custom_cake'] === 'true';
    
    ob_start();
    ?>
    <div class="tsb-pickup-scheduler" data-custom-cake="<?php echo $custom_cake ? 'true' : 'false'; ?>">
        <!-- Store Selection -->
        <div class="scheduler-step" data-step="1">
            <h4><?php _e('Select Pickup Location', 'theskybakery'); ?></h4>
            <div class="store-selection">
                <?php foreach ($stores as $store) : 
                    $address = get_post_meta($store->ID, '_tsb_store_address', true);
                    $selected = ($atts['store_id'] == $store->ID) ? 'selected' : '';
                ?>
                <div class="store-option <?php echo $selected; ?>" data-store-id="<?php echo esc_attr($store->ID); ?>">
                    <div class="store-radio">
                        <input type="radio" 
                               name="pickup_store" 
                               id="store-<?php echo $store->ID; ?>" 
                               value="<?php echo esc_attr($store->ID); ?>"
                               <?php checked(!empty($selected)); ?>>
                    </div>
                    <label for="store-<?php echo $store->ID; ?>">
                        <strong><?php echo esc_html($store->post_title); ?></strong>
                        <?php if ($address) : ?>
                        <span class="address"><?php echo esc_html($address); ?></span>
                        <?php endif; ?>
                        <span class="today-hours"><?php echo esc_html(tsb_get_store_today_hours($store->ID)); ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Date Selection -->
        <div class="scheduler-step" data-step="2" style="display: none;">
            <h4><?php _e('Select Pickup Date', 'theskybakery'); ?></h4>
            <div class="date-selection">
                <div class="dates-grid" id="pickup-dates">
                    <!-- Dates loaded via AJAX -->
                </div>
            </div>
            <input type="hidden" name="pickup_date" id="pickup-date-input" value="">
        </div>
        
        <!-- Time Selection -->
        <div class="scheduler-step" data-step="3" style="display: none;">
            <h4><?php _e('Select Pickup Time', 'theskybakery'); ?></h4>
            <div class="time-selection">
                <div class="times-grid" id="pickup-times">
                    <!-- Times loaded via AJAX -->
                </div>
            </div>
            <input type="hidden" name="pickup_time" id="pickup-time-input" value="">
        </div>
        
        <!-- Confirmation -->
        <div class="scheduler-step" data-step="4" style="display: none;">
            <h4><?php _e('Pickup Details', 'theskybakery'); ?></h4>
            <div class="pickup-confirmation" id="pickup-confirmation">
                <!-- Summary loaded via JS -->
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="scheduler-nav">
            <button type="button" class="btn btn-outline-secondary prev-step" style="display: none;">
                <i class="fas fa-arrow-left"></i> <?php _e('Back', 'theskybakery'); ?>
            </button>
            <button type="button" class="btn btn-primary next-step">
                <?php _e('Continue', 'theskybakery'); ?> <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
    
    <style>
    .tsb-pickup-scheduler {
        max-width: 600px;
        margin: 0 auto;
    }
    .scheduler-step {
        padding: 20px 0;
    }
    .scheduler-step h4 {
        margin-bottom: 20px;
        color: var(--tsb-secondary-color, #2c2c2c);
    }
    .store-option {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .store-option:hover,
    .store-option.selected {
        border-color: var(--tsb-primary-color, #c9a86c);
        background-color: rgba(201, 168, 108, 0.05);
    }
    .store-option .store-radio {
        margin-right: 15px;
        margin-top: 3px;
    }
    .store-option label {
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }
    .store-option .address {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    .store-option .today-hours {
        font-size: 13px;
        color: var(--tsb-primary-color, #c9a86c);
        margin-top: 5px;
    }
    .dates-grid,
    .times-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }
    .date-btn,
    .time-btn {
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    .date-btn:hover,
    .time-btn:hover,
    .date-btn.selected,
    .time-btn.selected {
        border-color: var(--tsb-primary-color, #c9a86c);
        background-color: var(--tsb-primary-color, #c9a86c);
        color: white;
    }
    .date-btn .day-name {
        display: block;
        font-size: 12px;
        opacity: 0.8;
    }
    .date-btn .date-display {
        display: block;
        font-weight: 600;
    }
    .pickup-confirmation {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
    }
    .pickup-confirmation p {
        margin: 10px 0;
    }
    .pickup-confirmation i {
        margin-right: 8px;
        color: var(--tsb-primary-color, #c9a86c);
    }
    .scheduler-nav {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    .scheduler-nav .btn {
        min-width: 120px;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var currentStep = 1;
        var selectedStore = <?php echo !empty($atts['store_id']) ? intval($atts['store_id']) : 'null'; ?>;
        var selectedDate = '';
        var selectedTime = '';
        var isCustomCake = <?php echo $custom_cake ? 'true' : 'false'; ?>;
        
        // Store selection
        $('.store-option').on('click', function() {
            $('.store-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
            selectedStore = $(this).data('store-id');
        });
        
        // Next step
        $('.next-step').on('click', function() {
            if (currentStep === 1 && !selectedStore) {
                alert('Please select a pickup location.');
                return;
            }
            
            if (currentStep === 2 && !selectedDate) {
                alert('Please select a pickup date.');
                return;
            }
            
            if (currentStep === 3 && !selectedTime) {
                alert('Please select a pickup time.');
                return;
            }
            
            if (currentStep < 4) {
                currentStep++;
                updateStep();
            }
        });
        
        // Previous step
        $('.prev-step').on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateStep();
            }
        });
        
        function updateStep() {
            $('.scheduler-step').hide();
            $('.scheduler-step[data-step="' + currentStep + '"]').show();
            
            if (currentStep > 1) {
                $('.prev-step').show();
            } else {
                $('.prev-step').hide();
            }
            
            if (currentStep === 2) {
                loadDates();
            } else if (currentStep === 3) {
                loadTimes();
            } else if (currentStep === 4) {
                showConfirmation();
                $('.next-step').text('<?php _e('Confirm', 'theskybakery'); ?>');
            }
        }
        
        function loadDates() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'tsb_get_pickup_dates',
                    store_id: selectedStore,
                    custom_cake: isCustomCake,
                    nonce: '<?php echo wp_create_nonce('tsb_pickup_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        response.data.forEach(function(date) {
                            html += '<button type="button" class="date-btn" data-date="' + date.date + '">';
                            html += '<span class="day-name">' + date.day_name + '</span>';
                            html += '<span class="date-display">' + date.display + '</span>';
                            html += '</button>';
                        });
                        $('#pickup-dates').html(html);
                        
                        // Date selection handler
                        $('.date-btn').on('click', function() {
                            $('.date-btn').removeClass('selected');
                            $(this).addClass('selected');
                            selectedDate = $(this).data('date');
                            $('#pickup-date-input').val(selectedDate);
                        });
                    }
                }
            });
        }
        
        function loadTimes() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'tsb_get_pickup_times',
                    store_id: selectedStore,
                    date: selectedDate,
                    nonce: '<?php echo wp_create_nonce('tsb_pickup_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        response.data.forEach(function(slot) {
                            html += '<button type="button" class="time-btn" data-time="' + slot.time + '">';
                            html += slot.display;
                            html += '</button>';
                        });
                        $('#pickup-times').html(html);
                        
                        // Time selection handler
                        $('.time-btn').on('click', function() {
                            $('.time-btn').removeClass('selected');
                            $(this).addClass('selected');
                            selectedTime = $(this).data('time');
                            $('#pickup-time-input').val(selectedTime);
                        });
                    }
                }
            });
        }
        
        function showConfirmation() {
            var storeName = $('.store-option.selected label strong').text();
            var storeAddress = $('.store-option.selected .address').text();
            var dateDisplay = $('.date-btn.selected .date-display').text();
            var dayName = $('.date-btn.selected .day-name').text();
            var timeDisplay = $('.time-btn.selected').text();
            
            var html = '<p><strong>' + storeName + '</strong></p>';
            html += '<p>' + storeAddress + '</p>';
            html += '<p><i class="far fa-calendar"></i> ' + dayName + ', ' + dateDisplay + '</p>';
            html += '<p><i class="far fa-clock"></i> ' + timeDisplay + '</p>';
            
            $('#pickup-confirmation').html(html);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('pickup_scheduler', 'tsb_pickup_scheduler_shortcode');

/**
 * AJAX handler to get pickup dates
 */
function tsb_ajax_get_pickup_dates() {
    check_ajax_referer('tsb_pickup_nonce', 'nonce');
    
    $store_id = isset($_POST['store_id']) ? intval($_POST['store_id']) : 0;
    $custom_cake = isset($_POST['custom_cake']) && $_POST['custom_cake'] === 'true';
    
    if (!$store_id) {
        wp_send_json_error('Invalid store ID');
    }
    
    $dates = tsb_get_available_pickup_dates($store_id, 14, $custom_cake);
    
    wp_send_json_success($dates);
}
add_action('wp_ajax_tsb_get_pickup_dates', 'tsb_ajax_get_pickup_dates');
add_action('wp_ajax_nopriv_tsb_get_pickup_dates', 'tsb_ajax_get_pickup_dates');

/**
 * AJAX handler to get pickup times
 */
function tsb_ajax_get_pickup_times_scheduler() {
    check_ajax_referer('tsb_pickup_nonce', 'nonce');
    
    $store_id = isset($_POST['store_id']) ? intval($_POST['store_id']) : 0;
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    
    if (!$store_id || !$date) {
        wp_send_json_error('Invalid parameters');
    }
    
    $slots = tsb_get_available_time_slots($store_id, $date);
    
    wp_send_json_success($slots);
}
add_action('wp_ajax_tsb_get_pickup_times', 'tsb_ajax_get_pickup_times_scheduler');
add_action('wp_ajax_nopriv_tsb_get_pickup_times', 'tsb_ajax_get_pickup_times_scheduler');

/**
 * Add pickup reminder meta box to orders
 */
function tsb_add_pickup_reminder_metabox() {
    add_meta_box(
        'tsb_pickup_reminder',
        __('Pickup Reminder', 'theskybakery'),
        'tsb_pickup_reminder_metabox_content',
        'shop_order',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'tsb_add_pickup_reminder_metabox');

/**
 * Pickup reminder metabox content
 */
function tsb_pickup_reminder_metabox_content($post) {
    $order = wc_get_order($post->ID);
    
    $store_id = $order->get_meta('_pickup_location');
    $date = $order->get_meta('_pickup_date');
    $time = $order->get_meta('_pickup_time');
    
    if (!$store_id || !$date) {
        echo '<p>' . __('No pickup information for this order.', 'theskybakery') . '</p>';
        return;
    }
    
    $store = get_post($store_id);
    $store_name = $store ? $store->post_title : __('Unknown', 'theskybakery');
    
    $formatted_date = date('l, F j, Y', strtotime($date));
    $formatted_time = $time ? date('g:i A', strtotime($time)) : '';
    
    echo '<div class="pickup-info">';
    echo '<p><strong>' . esc_html($store_name) . '</strong></p>';
    echo '<p>' . esc_html($formatted_date) . '</p>';
    if ($formatted_time) {
        echo '<p>' . esc_html($formatted_time) . '</p>';
    }
    echo '</div>';
    
    $reminder_sent = $order->get_meta('_pickup_reminder_sent');
    
    if ($reminder_sent) {
        echo '<p class="reminder-status sent"><span class="dashicons dashicons-yes"></span> ' . __('Reminder sent', 'theskybakery') . '</p>';
    } else {
        echo '<button type="button" class="button send-pickup-reminder" data-order-id="' . esc_attr($post->ID) . '">';
        echo __('Send Pickup Reminder', 'theskybakery');
        echo '</button>';
    }
}

/**
 * Schedule pickup reminders
 */
function tsb_schedule_pickup_reminders() {
    if (!wp_next_scheduled('tsb_check_pickup_reminders')) {
        wp_schedule_event(time(), 'hourly', 'tsb_check_pickup_reminders');
    }
}
add_action('wp', 'tsb_schedule_pickup_reminders');

/**
 * Check and send pickup reminders
 */
function tsb_check_and_send_reminders() {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    $orders = wc_get_orders(array(
        'limit'      => -1,
        'status'     => array('processing', 'on-hold'),
        'meta_query' => array(
            array(
                'key'     => '_pickup_date',
                'value'   => $tomorrow,
                'compare' => '=',
            ),
            array(
                'key'     => '_pickup_reminder_sent',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));
    
    foreach ($orders as $order) {
        tsb_send_pickup_reminder($order);
    }
}
add_action('tsb_check_pickup_reminders', 'tsb_check_and_send_reminders');

/**
 * Send pickup reminder email
 */
function tsb_send_pickup_reminder($order) {
    $store_id = $order->get_meta('_pickup_location');
    $date = $order->get_meta('_pickup_date');
    $time = $order->get_meta('_pickup_time');
    
    $store = get_post($store_id);
    $store_name = $store ? $store->post_title : '';
    $store_address = get_post_meta($store_id, '_tsb_store_address', true);
    $store_phone = get_post_meta($store_id, '_tsb_store_phone', true);
    
    $customer_email = $order->get_billing_email();
    $customer_name = $order->get_billing_first_name();
    
    $subject = sprintf(
        __('Reminder: Your order #%s is ready for pickup tomorrow', 'theskybakery'),
        $order->get_order_number()
    );
    
    $message = sprintf(
        __('Hi %s,

This is a friendly reminder that your order #%s is scheduled for pickup tomorrow.

Pickup Details:
Location: %s
Address: %s
Date: %s
Time: %s

Please bring your order confirmation when you arrive.

If you have any questions, please call us at %s.

Thank you for choosing The Sky Bakery!

Best regards,
The Sky Bakery Team', 'theskybakery'),
        $customer_name,
        $order->get_order_number(),
        $store_name,
        $store_address,
        date('l, F j, Y', strtotime($date)),
        $time ? date('g:i A', strtotime($time)) : 'During opening hours',
        $store_phone
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    $sent = wp_mail($customer_email, $subject, $message, $headers);
    
    if ($sent) {
        $order->update_meta_data('_pickup_reminder_sent', current_time('mysql'));
        $order->save();
    }
    
    return $sent;
}
