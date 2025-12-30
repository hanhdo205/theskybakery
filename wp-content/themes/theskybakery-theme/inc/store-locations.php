<?php
/**
 * Store Locations Helper Functions
 * 
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all store locations
 */
function tsb_get_stores($args = array()) {
    $defaults = array(
        'post_type'      => 'store_location',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return get_posts($args);
}

/**
 * Get store location data
 */
function tsb_get_store_data($store_id) {
    if (!$store_id) {
        return false;
    }
    
    $store = get_post($store_id);
    
    if (!$store || $store->post_type !== 'store_location') {
        return false;
    }
    
    return array(
        'id'        => $store_id,
        'name'      => $store->post_title,
        'address'   => get_post_meta($store_id, '_tsb_store_address', true),
        'phone'     => get_post_meta($store_id, '_tsb_store_phone', true),
        'email'     => get_post_meta($store_id, '_tsb_store_email', true),
        'hours'     => get_post_meta($store_id, '_tsb_store_hours', true),
        'map_embed' => get_post_meta($store_id, '_tsb_store_map_embed', true),
        'lat'       => get_post_meta($store_id, '_tsb_store_lat', true),
        'lng'       => get_post_meta($store_id, '_tsb_store_lng', true),
        'image'     => get_the_post_thumbnail_url($store_id, 'medium_large'),
    );
}

/**
 * Get stores as options array (for select dropdowns)
 */
function tsb_get_store_options() {
    $stores = tsb_get_stores();
    $options = array();
    
    foreach ($stores as $store) {
        $options[$store->ID] = $store->post_title;
    }
    
    return $options;
}

/**
 * Get store hours as array
 */
function tsb_get_store_hours_array($store_id) {
    $hours_string = get_post_meta($store_id, '_tsb_store_hours', true);
    
    if (empty($hours_string)) {
        // Default hours
        return array(
            'monday'    => array('open' => '07:00', 'close' => '17:00'),
            'tuesday'   => array('open' => '07:00', 'close' => '17:00'),
            'wednesday' => array('open' => '07:00', 'close' => '17:00'),
            'thursday'  => array('open' => '07:00', 'close' => '17:00'),
            'friday'    => array('open' => '07:00', 'close' => '17:00'),
            'saturday'  => array('open' => '08:00', 'close' => '16:00'),
            'sunday'    => array('open' => '09:00', 'close' => '15:00'),
        );
    }
    
    // Parse hours string if it's stored as serialized array
    $hours = maybe_unserialize($hours_string);
    
    if (is_array($hours)) {
        return $hours;
    }
    
    // Parse human-readable format
    return tsb_parse_hours_string($hours_string);
}

/**
 * Parse hours string into array
 */
function tsb_parse_hours_string($hours_string) {
    $default_hours = array(
        'monday'    => array('open' => '07:00', 'close' => '17:00'),
        'tuesday'   => array('open' => '07:00', 'close' => '17:00'),
        'wednesday' => array('open' => '07:00', 'close' => '17:00'),
        'thursday'  => array('open' => '07:00', 'close' => '17:00'),
        'friday'    => array('open' => '07:00', 'close' => '17:00'),
        'saturday'  => array('open' => '08:00', 'close' => '16:00'),
        'sunday'    => array('open' => '09:00', 'close' => '15:00'),
    );
    
    // Could implement parsing logic here if needed
    return $default_hours;
}

/**
 * Check if store is open
 */
function tsb_is_store_open($store_id, $timestamp = null) {
    if (!$timestamp) {
        $timestamp = current_time('timestamp');
    }
    
    $day = strtolower(date('l', $timestamp));
    $time = date('H:i', $timestamp);
    
    $hours = tsb_get_store_hours_array($store_id);
    
    if (!isset($hours[$day])) {
        return false;
    }
    
    $day_hours = $hours[$day];
    
    if (isset($day_hours['closed']) && $day_hours['closed']) {
        return false;
    }
    
    return ($time >= $day_hours['open'] && $time <= $day_hours['close']);
}

/**
 * Get store status text
 */
function tsb_get_store_status($store_id) {
    $is_open = tsb_is_store_open($store_id);
    
    if ($is_open) {
        return '<span class="store-status open"><i class="fas fa-circle"></i> Open Now</span>';
    } else {
        return '<span class="store-status closed"><i class="fas fa-circle"></i> Closed</span>';
    }
}

/**
 * Get today's hours for store
 */
function tsb_get_store_today_hours($store_id) {
    $hours = tsb_get_store_hours_array($store_id);
    $day = strtolower(date('l'));
    
    if (!isset($hours[$day])) {
        return 'Closed today';
    }
    
    $day_hours = $hours[$day];
    
    if (isset($day_hours['closed']) && $day_hours['closed']) {
        return 'Closed today';
    }
    
    $open = date('g:ia', strtotime($day_hours['open']));
    $close = date('g:ia', strtotime($day_hours['close']));
    
    return sprintf('Today: %s - %s', $open, $close);
}

/**
 * Format store hours for display
 */
function tsb_format_store_hours($store_id) {
    $hours = tsb_get_store_hours_array($store_id);
    
    $output = '<div class="store-hours-list">';
    
    $days = array(
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
        'sunday'    => 'Sunday',
    );
    
    foreach ($days as $key => $label) {
        $output .= '<div class="hours-row">';
        $output .= '<span class="day">' . esc_html($label) . '</span>';
        
        if (isset($hours[$key]['closed']) && $hours[$key]['closed']) {
            $output .= '<span class="times closed">Closed</span>';
        } elseif (isset($hours[$key]['open']) && isset($hours[$key]['close'])) {
            $open = date('g:ia', strtotime($hours[$key]['open']));
            $close = date('g:ia', strtotime($hours[$key]['close']));
            $output .= '<span class="times">' . esc_html($open) . ' - ' . esc_html($close) . '</span>';
        } else {
            $output .= '<span class="times">Not available</span>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get nearest store by coordinates
 */
function tsb_get_nearest_store($lat, $lng) {
    $stores = tsb_get_stores();
    $nearest = null;
    $min_distance = PHP_FLOAT_MAX;
    
    foreach ($stores as $store) {
        $store_lat = get_post_meta($store->ID, '_tsb_store_lat', true);
        $store_lng = get_post_meta($store->ID, '_tsb_store_lng', true);
        
        if (empty($store_lat) || empty($store_lng)) {
            continue;
        }
        
        $distance = tsb_calculate_distance($lat, $lng, $store_lat, $store_lng);
        
        if ($distance < $min_distance) {
            $min_distance = $distance;
            $nearest = $store;
        }
    }
    
    return $nearest;
}

/**
 * Calculate distance between two points (Haversine formula)
 */
function tsb_calculate_distance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // km
    
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $lng1 = deg2rad($lng1);
    $lng2 = deg2rad($lng2);
    
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earth_radius * $c;
}

/**
 * Store locator shortcode
 */
function tsb_store_locator_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_map' => 'true',
        'columns'  => '2',
    ), $atts, 'store_locator');
    
    $stores = tsb_get_stores();
    
    if (empty($stores)) {
        return '<p>No store locations found.</p>';
    }
    
    ob_start();
    ?>
    <div class="tsb-store-locator" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        <?php if ($atts['show_map'] === 'true') : ?>
        <div class="store-locator-map" id="stores-map">
            <!-- Map will be initialized via JavaScript -->
        </div>
        <?php endif; ?>
        
        <div class="store-locator-list">
            <?php foreach ($stores as $store) : 
                $data = tsb_get_store_data($store->ID);
            ?>
            <div class="store-card" data-lat="<?php echo esc_attr($data['lat']); ?>" data-lng="<?php echo esc_attr($data['lng']); ?>">
                <?php if ($data['image']) : ?>
                <div class="store-image">
                    <img src="<?php echo esc_url($data['image']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                </div>
                <?php endif; ?>
                
                <div class="store-info">
                    <h3><?php echo esc_html($data['name']); ?></h3>
                    
                    <?php echo tsb_get_store_status($store->ID); ?>
                    
                    <p class="today-hours"><?php echo esc_html(tsb_get_store_today_hours($store->ID)); ?></p>
                    
                    <?php if ($data['address']) : ?>
                    <p class="address">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($data['address']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($data['phone']) : ?>
                    <p class="phone">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $data['phone'])); ?>">
                            <?php echo esc_html($data['phone']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    
                    <div class="store-actions">
                        <a href="<?php echo esc_url(get_permalink($store->ID)); ?>" class="btn btn-primary btn-sm">
                            View Details
                        </a>
                        <?php if ($data['lat'] && $data['lng']) : ?>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($data['lat']); ?>,<?php echo esc_attr($data['lng']); ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm">
                            Get Directions
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('store_locator', 'tsb_store_locator_shortcode');

/**
 * Single store shortcode
 */
function tsb_single_store_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'store');
    
    if (empty($atts['id'])) {
        return '';
    }
    
    $data = tsb_get_store_data($atts['id']);
    
    if (!$data) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="tsb-single-store">
        <div class="store-header">
            <h3><?php echo esc_html($data['name']); ?></h3>
            <?php echo tsb_get_store_status($atts['id']); ?>
        </div>
        
        <div class="store-details">
            <?php if ($data['address']) : ?>
            <div class="detail-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo esc_html($data['address']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($data['phone']) : ?>
            <div class="detail-item">
                <i class="fas fa-phone"></i>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $data['phone'])); ?>">
                    <?php echo esc_html($data['phone']); ?>
                </a>
            </div>
            <?php endif; ?>
            
            <?php if ($data['email']) : ?>
            <div class="detail-item">
                <i class="fas fa-envelope"></i>
                <a href="mailto:<?php echo esc_attr($data['email']); ?>">
                    <?php echo esc_html($data['email']); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="store-hours">
            <h4>Opening Hours</h4>
            <?php echo tsb_format_store_hours($atts['id']); ?>
        </div>
        
        <?php if ($data['map_embed']) : ?>
        <div class="store-map">
            <?php echo wp_kses($data['map_embed'], array(
                'iframe' => array(
                    'src'             => true,
                    'width'           => true,
                    'height'          => true,
                    'frameborder'     => true,
                    'style'           => true,
                    'allowfullscreen' => true,
                    'loading'         => true,
                ),
            )); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('store', 'tsb_single_store_shortcode');

/**
 * Store phone list (for footer)
 */
function tsb_store_phones_list() {
    $stores = tsb_get_stores();
    
    if (empty($stores)) {
        return;
    }
    
    echo '<ul class="store-phones">';
    foreach ($stores as $store) {
        $phone = get_post_meta($store->ID, '_tsb_store_phone', true);
        if ($phone) {
            printf(
                '<li><strong>%s:</strong> <a href="tel:%s">%s</a></li>',
                esc_html($store->post_title),
                esc_attr(preg_replace('/[^0-9+]/', '', $phone)),
                esc_html($phone)
            );
        }
    }
    echo '</ul>';
}

/**
 * Get stores for pickup selection
 */
function tsb_get_pickup_stores() {
    $stores = tsb_get_stores();
    $pickup_stores = array();
    
    foreach ($stores as $store) {
        $pickup_stores[] = array(
            'id'      => $store->ID,
            'name'    => $store->post_title,
            'address' => get_post_meta($store->ID, '_tsb_store_address', true),
            'phone'   => get_post_meta($store->ID, '_tsb_store_phone', true),
        );
    }
    
    return $pickup_stores;
}
