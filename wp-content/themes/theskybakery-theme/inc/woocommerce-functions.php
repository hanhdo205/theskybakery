<?php
/**
 * WooCommerce Functions
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

/**
 * ========================================
 * PICKUP ONLY STORE - DISABLE SHIPPING
 * ========================================
 */

/**
 * Disable shipping - all products are pickup only
 */
add_filter('woocommerce_cart_needs_shipping', '__return_false');

/**
 * Hide shipping fields in checkout
 */
add_filter('woocommerce_checkout_fields', function($fields) {
    unset($fields['shipping']);
    return $fields;
});

/**
 * Remove shipping from checkout blocks
 */
add_filter('woocommerce_blocks_checkout_requires_shipping', '__return_false');

/**
 * Set all products as virtual (no shipping needed)
 */
add_filter('woocommerce_product_needs_shipping', '__return_false');

/**
 * ========================================
 * AJAX HANDLER FOR PICKUP DATE
 * ========================================
 */

/**
 * Save pickup date to session via AJAX (with cookie backup)
 */
function tsb_save_pickup_date_ajax() {
    $pickup_date = isset($_POST['pickup_date']) ? sanitize_text_field($_POST['pickup_date']) : '';

    error_log('TSB AJAX - Received pickup_date: ' . $pickup_date);

    if (!empty($pickup_date)) {
        // Method 1: Save to cookie (most reliable for Store API)
        setcookie('tsb_pickup_date', $pickup_date, time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
        $_COOKIE['tsb_pickup_date'] = $pickup_date; // Also set in current request
        error_log('TSB AJAX - Saved to cookie: ' . $pickup_date);

        // Method 2: Try WC session
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('tsb_pickup_date', $pickup_date);
            error_log('TSB AJAX - Saved to session: ' . $pickup_date);
        }

        wp_send_json_success(array('saved' => true, 'date' => $pickup_date));
    } else {
        wp_send_json_error(array('message' => 'No date provided'));
    }
}
add_action('wp_ajax_tsb_save_pickup_date', 'tsb_save_pickup_date_ajax');
add_action('wp_ajax_nopriv_tsb_save_pickup_date', 'tsb_save_pickup_date_ajax');

/**
 * Disable default WooCommerce styles (but keep star rating font)
 */
add_filter('woocommerce_enqueue_styles', function($enqueue_styles) {
    // Remove all default styles except star rating font
    unset($enqueue_styles['woocommerce-general']);
    unset($enqueue_styles['woocommerce-layout']);
    unset($enqueue_styles['woocommerce-smallscreen']);

    // Keep star rating font
    return $enqueue_styles;
});

/**
 * Hide duplicate price in checkout order summary
 */
add_action('wp_head', function() {
    if (is_checkout()) {
        ?>
        <style>
        .wc-block-components-order-summary-item__individual-price {
            display: none !important;
        }
		
		.wc-block-components-address-form__country{
            display: none !important;
        }
		
		.billing_hidden {
			display: none !important;
		}
        </style>
	   <?php
    }
});

/**
 * Add custom wrapper
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

function tsb_woocommerce_wrapper_before() {
    ?>
    <main id="primary" class="site-main woocommerce-page">
        <div class="container">
    <?php
}
add_action('woocommerce_before_main_content', 'tsb_woocommerce_wrapper_before');

function tsb_woocommerce_wrapper_after() {
    ?>
        </div>
    </main>
    <?php
}
add_action('woocommerce_after_main_content', 'tsb_woocommerce_wrapper_after');

/**
 * Remove sidebar from shop
 */
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/**
 * Change number of products per row
 */
add_filter('loop_shop_columns', function() {
    return 4;
});

/**
 * Change number of products per page
 */
add_filter('loop_shop_per_page', function() {
    return 24;
});

/**
 * Customize product thumbnails
 */
function tsb_woocommerce_product_thumbnail() {
    global $product;
    
    $placeholder = wc_placeholder_img_src('tsb-product-thumb');
    $image = '';

    if ($product->get_image_id()) {
        $image = wp_get_attachment_image($product->get_image_id(), 'tsb-product-thumb', false, array(
            'class' => 'attachment-tsb-product-thumb',
        ));
    } else {
        $image = '<img src="' . esc_url($placeholder) . '" alt="' . esc_attr($product->get_name()) . '" class="wp-post-image">';
    }

    echo '<div class="product-thumbnail">' . $image . '</div>';
}

/**
 * Add pickup location to checkout
 */
function tsb_add_pickup_location_field($checkout) {
    $stores = tsb_get_stores();
    $options = array('' => __('Select pickup location', 'theskybakery'));
    
    if ($stores) {
        foreach ($stores as $store) {
            $options[$store->ID] = $store->post_title;
        }
    }

    echo '<div class="tsb-pickup-section">';
    echo '<h3>' . __('Pickup Details', 'theskybakery') . '</h3>';
    
    woocommerce_form_field('pickup_location', array(
        'type'     => 'select',
        'class'    => array('form-row-wide'),
        'label'    => __('Pickup Location', 'theskybakery'),
        'required' => true,
        'options'  => $options,
    ), $checkout->get_value('pickup_location'));

    woocommerce_form_field('pickup_date', array(
        'type'     => 'date',
        'class'    => array('form-row-first'),
        'label'    => __('Pickup Date', 'theskybakery'),
        'required' => true,
    ), $checkout->get_value('pickup_date'));

    woocommerce_form_field('pickup_time', array(
        'type'     => 'select',
        'class'    => array('form-row-last'),
        'label'    => __('Pickup Time', 'theskybakery'),
        'required' => true,
        'options'  => tsb_get_pickup_time_options(),
    ), $checkout->get_value('pickup_time'));

    echo '</div>';
}
add_action('woocommerce_after_order_notes', 'tsb_add_pickup_location_field');

/**
 * Get pickup time options
 */
function tsb_get_pickup_time_options() {
    $options = array('' => __('Select time', 'theskybakery'));
    
    $start = 8; // 7 AM
    $end = 17;  // 5 PM
    
    for ($hour = $start; $hour <= $end; $hour++) {
        for ($min = 0; $min < 60; $min += 30) {
            $time = sprintf('%02d:%02d', $hour, $min);
            $label = date('g:i A', strtotime($time));
            $options[$time] = $label;
        }
    }

    return $options;
}

/**
 * Validate pickup fields
 */
function tsb_validate_pickup_fields() {
    if (empty($_POST['pickup_location'])) {
        wc_add_notice(__('Please select a pickup location.', 'theskybakery'), 'error');
    }
    
    if (empty($_POST['pickup_date'])) {
        wc_add_notice(__('Please select a pickup date.', 'theskybakery'), 'error');
    } else {
        $pickup_date = strtotime($_POST['pickup_date']);
        $tomorrow = strtotime('tomorrow');
        
        if ($pickup_date < $tomorrow) {
            wc_add_notice(__('Pickup date must be at least tomorrow.', 'theskybakery'), 'error');
        }
    }
    
    if (empty($_POST['pickup_time'])) {
        wc_add_notice(__('Please select a pickup time.', 'theskybakery'), 'error');
    }
}
add_action('woocommerce_checkout_process', 'tsb_validate_pickup_fields');

/**
 * Save pickup fields to order
 */
function tsb_save_pickup_fields($order_id) {
    if (!empty($_POST['pickup_location'])) {
        update_post_meta($order_id, '_pickup_location', absint($_POST['pickup_location']));
    }
    
    if (!empty($_POST['pickup_date'])) {
        update_post_meta($order_id, '_pickup_date', sanitize_text_field($_POST['pickup_date']));
    }
    
    if (!empty($_POST['pickup_time'])) {
        update_post_meta($order_id, '_pickup_time', sanitize_text_field($_POST['pickup_time']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'tsb_save_pickup_fields');

/**
 * Display pickup details on order admin
 */
function tsb_display_pickup_on_order($order) {
    $location_id = get_post_meta($order->get_id(), '_pickup_location', true);
    $date = get_post_meta($order->get_id(), '_pickup_date', true);
    $time = get_post_meta($order->get_id(), '_pickup_time', true);

    if ($location_id || $date || $time) {
        echo '<h3>' . __('Pickup Details', 'theskybakery') . '</h3>';
        
        if ($location_id) {
            $store = get_post($location_id);
            if ($store) {
                echo '<p><strong>' . __('Location:', 'theskybakery') . '</strong> ' . esc_html($store->post_title) . '</p>';
            }
        }
        
        if ($date) {
            echo '<p><strong>' . __('Date:', 'theskybakery') . '</strong> ' . date('F j, Y', strtotime($date)) . '</p>';
        }
        
        if ($time) {
            echo '<p><strong>' . __('Time:', 'theskybakery') . '</strong> ' . date('g:i A', strtotime($time)) . '</p>';
        }
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'tsb_display_pickup_on_order');

/**
 * Display pickup details on order received (thank you) page
 */
function tsb_display_pickup_on_thankyou($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $location_id = $order->get_meta('_pickup_location');
    $date = $order->get_meta('_pickup_date');
    $time = $order->get_meta('_pickup_time');

    if ($location_id || $date || $time) {
        echo '<section class="woocommerce-pickup-details">';
        echo '<h2>' . __('Pickup Details', 'theskybakery') . '</h2>';

        if ($location_id) {
            $store = get_post($location_id);
            if ($store) {
                $address = get_post_meta($location_id, '_store_address', true);
                echo '<p><strong>' . __('Location:', 'theskybakery') . '</strong> ' . esc_html($store->post_title) . '</p>';
                if ($address) {
                    echo '<p><strong>' . __('Address:', 'theskybakery') . '</strong> ' . esc_html($address) . '</p>';
                }
            }
        }

        if ($date) {
            echo '<p><strong>' . __('Date:', 'theskybakery') . '</strong> ' . date('F j, Y', strtotime($date)) . '</p>';
        }

        if ($time) {
            echo '<p><strong>' . __('Time:', 'theskybakery') . '</strong> ' . date('g:i A', strtotime($time)) . '</p>';
        }

        echo '</section>';
    }
}
add_action('woocommerce_thankyou', 'tsb_display_pickup_on_thankyou', 5);

/**
 * Add pickup details to order emails
 */
function tsb_add_pickup_to_emails($order, $sent_to_admin, $plain_text, $email) {
    $location_id = get_post_meta($order->get_id(), '_pickup_location', true);
    $date = get_post_meta($order->get_id(), '_pickup_date', true);
    $time = get_post_meta($order->get_id(), '_pickup_time', true);

    if ($location_id || $date || $time) {
        if ($plain_text) {
            echo "\n\n" . __('PICKUP DETAILS', 'theskybakery') . "\n";
        } else {
            echo '<h2>' . __('Pickup Details', 'theskybakery') . '</h2>';
        }
        
        if ($location_id) {
            $store = get_post($location_id);
            $address = get_post_meta($location_id, '_store_address', true);
            $phone = get_post_meta($location_id, '_store_phone', true);
            
            if ($store) {
                if ($plain_text) {
                    echo __('Location:', 'theskybakery') . ' ' . $store->post_title . "\n";
                    if ($address) echo __('Address:', 'theskybakery') . ' ' . $address . "\n";
                    if ($phone) echo __('Phone:', 'theskybakery') . ' ' . $phone . "\n";
                } else {
                    echo '<p><strong>' . __('Location:', 'theskybakery') . '</strong> ' . esc_html($store->post_title) . '</p>';
                    if ($address) echo '<p><strong>' . __('Address:', 'theskybakery') . '</strong> ' . esc_html($address) . '</p>';
                    if ($phone) echo '<p><strong>' . __('Phone:', 'theskybakery') . '</strong> ' . esc_html($phone) . '</p>';
                }
            }
        }
        
        if ($date) {
            if ($plain_text) {
                echo __('Date:', 'theskybakery') . ' ' . date('F j, Y', strtotime($date)) . "\n";
            } else {
                echo '<p><strong>' . __('Date:', 'theskybakery') . '</strong> ' . date('F j, Y', strtotime($date)) . '</p>';
            }
        }
        
        if ($time) {
            if ($plain_text) {
                echo __('Time:', 'theskybakery') . ' ' . date('g:i A', strtotime($time)) . "\n";
            } else {
                echo '<p><strong>' . __('Time:', 'theskybakery') . '</strong> ' . date('g:i A', strtotime($time)) . '</p>';
            }
        }
    }
}
add_action('woocommerce_email_after_order_table', 'tsb_add_pickup_to_emails', 10, 4);

/**
 * Add product categories to body class
 */
function tsb_product_category_body_class($classes) {
    if (is_product_category()) {
        $cat = get_queried_object();
        $classes[] = 'product-category-' . $cat->slug;
    }
    return $classes;
}
add_filter('body_class', 'tsb_product_category_body_class');

/**
 * Customize related products
 */
function tsb_related_products_args($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'tsb_related_products_args');

/**
 * Add custom product data for cake customization
 */
function tsb_add_cake_custom_fields() {
    global $woocommerce, $post;

    echo '<div class="options_group">';

    woocommerce_wp_checkbox(array(
        'id'          => '_is_customizable_cake',
        'label'       => __('Customizable Cake', 'theskybakery'),
        'description' => __('Enable cake customization for this product', 'theskybakery'),
    ));

    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'tsb_add_cake_custom_fields');

/**
 * Save cake custom fields
 */
function tsb_save_cake_custom_fields($post_id) {
    $is_customizable = isset($_POST['_is_customizable_cake']) ? 'yes' : 'no';
    update_post_meta($post_id, '_is_customizable_cake', $is_customizable);
}
add_action('woocommerce_process_product_meta', 'tsb_save_cake_custom_fields');

/**
 * ========================================
 * PRODUCT DETAIL PAGE CUSTOMIZATIONS
 * ========================================
 */

/**
 * Wrap product meta in a styled container
 */
add_action('woocommerce_single_product_summary', function() {
    echo '<div class="product-meta-info">';
}, 4);

add_action('woocommerce_single_product_summary', function() {
    echo '</div>';
}, 11);

/**
 * Add stock status badge
 */
add_action('woocommerce_single_product_summary', function() {
    global $product;

    if ($product->is_in_stock()) {
        echo '<span class="stock-badge in-stock"><i class="fas fa-check-circle"></i> ' . __('In Stock', 'theskybakery') . '</span>';
    } else {
        echo '<span class="stock-badge out-of-stock"><i class="fas fa-times-circle"></i> ' . __('Out of Stock', 'theskybakery') . '</span>';
    }
}, 9);

/**
 * Customize product tabs
 */
add_filter('woocommerce_product_tabs', function($tabs) {

    // Rename Description tab
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = __('Product Details', 'theskybakery');
        $tabs['description']['priority'] = 10;
    }

    // Rename Additional Information tab
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = __('Specifications', 'theskybakery');
        $tabs['additional_information']['priority'] = 20;
    }

    // Rename Reviews tab
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['title'] = __('Customer Reviews', 'theskybakery');
        $tabs['reviews']['priority'] = 30;
    }

    // Add custom tab for bakery info
    $tabs['bakery_info'] = array(
        'title'    => __('Bakery Info', 'theskybakery'),
        'priority' => 40,
        'callback' => 'tsb_bakery_info_tab_content'
    );

    return $tabs;
});

/**
 * Bakery info tab content
 */
function tsb_bakery_info_tab_content() {
    ?>
    <div class="bakery-info-content">
        <h3><?php _e('About Our Bakery', 'theskybakery'); ?></h3>
        <p><?php _e('All our products are freshly baked using premium ingredients. We take pride in our traditional recipes combined with modern techniques.', 'theskybakery'); ?></p>

        <div class="bakery-features-list">
            <div class="feature-row">
                <i class="fas fa-leaf"></i>
                <div>
                    <h4><?php _e('Fresh Ingredients', 'theskybakery'); ?></h4>
                    <p><?php _e('We use only the freshest, highest quality ingredients.', 'theskybakery'); ?></p>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-clock"></i>
                <div>
                    <h4><?php _e('Baked Daily', 'theskybakery'); ?></h4>
                    <p><?php _e('All products are baked fresh every morning.', 'theskybakery'); ?></p>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-heart"></i>
                <div>
                    <h4><?php _e('Made with Love', 'theskybakery'); ?></h4>
                    <p><?php _e('Each product is crafted with care and attention to detail.', 'theskybakery'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Change related products heading
 */
add_filter('woocommerce_product_related_products_heading', function() {
    return __('You May Also Like', 'theskybakery');
});

/**
 * Add sale badge
 */
add_filter('woocommerce_sale_flash', function($html, $post, $product) {
    if ($product->is_on_sale()) {
        $percentage = '';
        if ($product->get_regular_price()) {
            $percentage = round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100);
        }

        if ($percentage) {
            return '<span class="onsale"><span class="sale-text">Sale</span><span class="sale-percentage">-' . $percentage . '%</span></span>';
        }
    }
    return $html;
}, 10, 3);

/**
 * Add to cart message
 */
add_filter('wc_add_to_cart_message_html', function($message, $products) {
    $message = sprintf(
        '<div class="woocommerce-message"><i class="fas fa-check-circle"></i> %s <a href="%s" class="button wc-forward">%s</a></div>',
        __('Product successfully added to cart!', 'theskybakery'),
        wc_get_cart_url(),
        __('View Cart', 'theskybakery')
    );
    return $message;
}, 10, 2);

/**
 * Replace star rating HTML with Font Awesome icons
 */
add_filter('woocommerce_product_get_rating_html', 'tsb_custom_star_rating_html', 10, 3);
add_filter('woocommerce_product_variation_get_rating_html', 'tsb_custom_star_rating_html', 10, 3);

function tsb_custom_star_rating_html($html, $rating, $count) {
    if ($rating > 0) {
        $stars_html = '<div class="star-rating" role="img" aria-label="' . sprintf(__('Rated %s out of 5', 'theskybakery'), $rating) . '">';

        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.5;
        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

        // Full stars
        for ($i = 0; $i < $full_stars; $i++) {
            $stars_html .= '<i class="fas fa-star"></i>';
        }

        // Half star
        if ($half_star) {
            $stars_html .= '<i class="fas fa-star-half-alt"></i>';
        }

        // Empty stars
        for ($i = 0; $i < $empty_stars; $i++) {
            $stars_html .= '<i class="far fa-star"></i>';
        }

        $stars_html .= '</div>';

        return $stars_html;
    }

    return '';
}

/**
 * Override review display rating with Font Awesome stars
 */
add_action('woocommerce_review_before_comment_meta', function($comment) {
    // Remove default rating display
    remove_action('woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10);
}, 1);

add_action('woocommerce_review_before_comment_meta', function($comment) {
    $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));

    if ($rating && wc_review_ratings_enabled()) {
        echo tsb_custom_star_rating_html('', $rating, 0);
    }
}, 10);

/**
 * Remove default WooCommerce rating field
 */
remove_action('comment_form_logged_in_after', 'woocommerce_comment_form_rating', 10);

/**
 * Add custom rating field with Font Awesome stars
 */
add_action('comment_form_logged_in_after', 'tsb_add_rating_field', 10);
add_action('comment_form_after_fields', 'tsb_add_rating_field', 10);

function tsb_add_rating_field() {
    if (!is_product()) {
        return;
    }

    // Get comment if we're editing
    $comment_id = isset($_GET['comment']) ? absint($_GET['comment']) : 0;
    $rating = $comment_id ? get_comment_meta($comment_id, 'rating', true) : '';

    ?>
    <div class="tsb-comment-form-rating">
        <label for="rating"><?php esc_html_e('Your rating', 'theskybakery'); ?>&nbsp;<span class="required">*</span></label>
        <div class="stars-input">
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <a href="#" class="star-link" data-rating="<?php echo $i; ?>">
                    <i class="far fa-star"></i>
                </a>
            <?php endfor; ?>
        </div>
        <select name="rating" id="rating" required style="display: none;">
            <option value=""><?php esc_html_e('Rate&hellip;', 'theskybakery'); ?></option>
            <option value="5" <?php selected($rating, '5'); ?>><?php esc_html_e('Perfect', 'theskybakery'); ?></option>
            <option value="4" <?php selected($rating, '4'); ?>><?php esc_html_e('Good', 'theskybakery'); ?></option>
            <option value="3" <?php selected($rating, '3'); ?>><?php esc_html_e('Average', 'theskybakery'); ?></option>
            <option value="2" <?php selected($rating, '2'); ?>><?php esc_html_e('Not that bad', 'theskybakery'); ?></option>
            <option value="1" <?php selected($rating, '1'); ?>><?php esc_html_e('Very poor', 'theskybakery'); ?></option>
        </select>
    </div>
    <?php
}

/**
 * ========================================
 * WOOCOMMERCE BLOCKS CHECKOUT FIELDS
 * ========================================
 * Add pickup fields for WooCommerce Block Checkout
 */

/**
 * Register Store API extension for pickup data
 */
function tsb_register_store_api_extension() {
    if (!function_exists('woocommerce_store_api_register_endpoint_data')) {
        return;
    }

    woocommerce_store_api_register_endpoint_data(
        array(
            'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
            'namespace'       => 'theskybakery',
            'data_callback'   => function() {
                return array(
                    'pickup-date' => '',
                );
            },
            'schema_callback' => function() {
                return array(
                    'pickup-date' => array(
                        'description' => __('Pickup date', 'theskybakery'),
                        'type'        => 'string',
                        'context'     => array('view', 'edit'),
                    ),
                );
            },
            'schema_type'     => 'ARRAY',
        )
    );
}
add_action('woocommerce_blocks_loaded', 'tsb_register_store_api_extension');


/**
 * Register pickup fields for WooCommerce Blocks Checkout
 */
function tsb_register_checkout_pickup_fields() {
    // Check if function exists (WooCommerce 8.6+)
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }

    // Get store options for dropdown
    $store_options = array();
    $stores = tsb_get_stores();
    if ($stores) {
        foreach ($stores as $store) {
            $store_options[] = array(
                'value' => (string) $store->ID,
                'label' => $store->post_title,
            );
        }
    }

    // Generate time slot options
    $time_options = array();
    $start = 9; // 7 AM
    $end = 16;  // 5 PM
    for ($hour = $start; $hour <= $end; $hour++) {
        for ($min = 0; $min < 60; $min += 30) {
            $time = sprintf('%02d:%02d', $hour, $min);
            $label = date('g:i A', strtotime($time));
            $time_options[] = array(
                'value' => $time,
                'label' => $label,
            );
        }
    }

    // Register Pickup Location field
    woocommerce_register_additional_checkout_field(
        array(
            'id'       => 'theskybakery/pickup-location',
            'label'    => __('Pickup Location', 'theskybakery'),
            'location' => 'order',
            'type'     => 'select',
            'required' => true,
            'options'  => $store_options,
        )
    );

    // Register Pickup Date field (text field, converted to date picker via JS)
    // Note: required is false to avoid WooCommerce's built-in validation issue with flatpickr
    // We validate manually via extension data
    woocommerce_register_additional_checkout_field(
        array(
            'id'                => 'theskybakery/pickup-date',
            'label'             => __('Pickup Date', 'theskybakery'),
            'location'          => 'order',
            'type'              => 'text',
            'required'          => false,
            'sanitize_callback' => 'tsb_sanitize_pickup_date',
        )
    );

    // Register Pickup Time field
    woocommerce_register_additional_checkout_field(
        array(
            'id'       => 'theskybakery/pickup-time',
            'label'    => __('Pickup Time', 'theskybakery'),
            'location' => 'order',
            'type'     => 'select',
            'required' => true,
            'options'  => $time_options,
        )
    );
}
add_action('woocommerce_blocks_loaded', 'tsb_register_checkout_pickup_fields');
add_action('woocommerce_init', 'tsb_register_checkout_pickup_fields');

/**
 * Sanitize pickup date field
 */
function tsb_sanitize_pickup_date($value) {
    // Log for debugging
    error_log('TSB Pickup Date Sanitize - Raw value: ' . print_r($value, true));

    if (empty($value)) {
        return '';
    }

    // Accept Y-m-d format
    $value = sanitize_text_field($value);

    // Try to parse the date
    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return $value;
}

/**
 * Validate pickup date callback for field registration
 */
function tsb_validate_pickup_date_callback($value) {
    // Log for debugging
    error_log('TSB Pickup Date Validate Callback - Value: ' . print_r($value, true));

    // Return true if valid, WP_Error if invalid
    if (empty($value)) {
        return new \WP_Error('pickup_date_required', __('Please select a pickup date.', 'theskybakery'));
    }

    $pickup_date = strtotime($value);
    if ($pickup_date === false) {
        return new \WP_Error('pickup_date_invalid', __('Please enter a valid date format.', 'theskybakery'));
    }

    $min_date = strtotime('+3 days');
    if ($pickup_date < $min_date) {
        return new \WP_Error('pickup_date_too_soon', __('Pickup date must be at least 3 days from now.', 'theskybakery'));
    }

    return true;
}


/**
 * Save pickup fields to order meta (for Blocks Checkout)
 */
function tsb_save_blocks_checkout_pickup_fields($order, $request) {
    $extensions = $request['extensions'] ?? array();
    $additional_fields = $request['additional_fields'] ?? array();

    // Log for debugging
    error_log('=== TSB Save Pickup Fields ===');
    error_log('TSB Save - Order ID: ' . $order->get_id());
    error_log('TSB Save - Extensions: ' . print_r($extensions, true));
    error_log('TSB Save - Additional Fields: ' . print_r($additional_fields, true));
    error_log('TSB Save - Cookie: ' . (isset($_COOKIE['tsb_pickup_date']) ? $_COOKIE['tsb_pickup_date'] : 'NOT SET'));

    // Get values from additional fields
    $pickup_location = $additional_fields['theskybakery/pickup-location'] ?? '';
    $pickup_date = $additional_fields['theskybakery/pickup-date'] ?? '';
    $pickup_time = $additional_fields['theskybakery/pickup-time'] ?? '';

    // Fallback 1: check extension data from theskybakery namespace
    if (empty($pickup_date) && isset($extensions['theskybakery']['pickup-date'])) {
        $pickup_date = $extensions['theskybakery']['pickup-date'];
        error_log('TSB Save - Got pickup_date from extensions: ' . $pickup_date);
    }

    // Fallback 2: check cookie (most reliable with Store API)
    if (empty($pickup_date) && isset($_COOKIE['tsb_pickup_date']) && !empty($_COOKIE['tsb_pickup_date'])) {
        $pickup_date = sanitize_text_field($_COOKIE['tsb_pickup_date']);
        error_log('TSB Save - Got pickup_date from cookie: ' . $pickup_date);
    }

    // Fallback 3: check WooCommerce session
    if (empty($pickup_date) && function_exists('WC') && WC()->session) {
        $pickup_date = WC()->session->get('tsb_pickup_date', '');
        if (!empty($pickup_date)) {
            error_log('TSB Save - Got pickup_date from session: ' . $pickup_date);
        }
    }

    error_log('TSB Save - Final values - Location: ' . $pickup_location . ', Date: ' . $pickup_date . ', Time: ' . $pickup_time);

    if (!empty($pickup_location)) {
        $order->update_meta_data('_pickup_location', absint($pickup_location));
    }

    if (!empty($pickup_date)) {
        $order->update_meta_data('_pickup_date', sanitize_text_field($pickup_date));
        // Clear cookie after saving
        setcookie('tsb_pickup_date', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    if (!empty($pickup_time)) {
        $order->update_meta_data('_pickup_time', sanitize_text_field($pickup_time));
    }

    // Clear session after saving
    if (function_exists('WC') && WC()->session) {
        WC()->session->set('tsb_pickup_date', '');
    }

    error_log('=== TSB Save Complete ===');
}
add_action('woocommerce_store_api_checkout_update_order_from_request', 'tsb_save_blocks_checkout_pickup_fields', 10, 2);

/**
 * Enqueue Flatpickr for checkout date picker
 */
function tsb_enqueue_checkout_datepicker() {
    if (!is_checkout()) {
        return;
    }

    // Flatpickr CSS
    wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13');

    // Flatpickr JS
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true);
}
add_action('wp_enqueue_scripts', 'tsb_enqueue_checkout_datepicker');

/**
 * Add Flatpickr initialization script for pickup date
 */
function tsb_checkout_pickup_date_script() {
    if (!is_checkout()) {
        return;
    }

    $min_date = date('Y-m-d', strtotime('+3 day'));
    ?>
    <style>
    /* Hide original input when flatpickr alt input is present */
    .tsb-flatpickr-wrapper input[id*="pickup-date"].flatpickr-input {
        display: none !important;
    }
    /* Style flatpickr alt input to match WooCommerce Blocks */
    .tsb-flatpickr-wrapper input.form-control {
        width: 100%;
        height: auto;
        padding: 1.5rem 1rem 0.5rem;
        border: 1px solid #8d8d8d;
        border-radius: 4px;
        font-size: 1rem;
        background: #fff;
        cursor: pointer;
        box-sizing: border-box;
    }
    .tsb-flatpickr-wrapper input.form-control:focus {
        border-color: #000;
        outline: none;
        box-shadow: none;
    }
    /* Move label up like select fields */
    .tsb-flatpickr-wrapper.is-active label {
        top: 0.25em !important;
        line-height: 1.125 !important;
    }
    /* Placeholder styling */
    .tsb-flatpickr-wrapper input::placeholder {
        color: #1e1e1e !important;
        opacity: 1 !important;
    }
    /* Flatpickr theme customization */
    .flatpickr-calendar {
        font-family: inherit;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 9999 !important;
    }
    .flatpickr-calendar .flatpickr-months {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .flatpickr-calendar .flatpickr-month {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        color: #1e1e1e !important;
    }
    .flatpickr-calendar .flatpickr-current-month {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        visibility: visible !important;
        opacity: 1 !important;
        color: #1e1e1e !important;
        font-size: 1rem !important;
        font-weight: 600;
        padding: 14px 0 5px !important;
    }
    .flatpickr-calendar .flatpickr-current-month span.cur-month,
    .flatpickr-calendar .flatpickr-current-month select.flatpickr-monthDropdown-months,
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper,
    .flatpickr-calendar .flatpickr-current-month input.cur-year {
        display: inline-block !important;
        vertical-align: middle !important;
        color: #1e1e1e !important;
        font-weight: 500 !important;
    }
    .flatpickr-calendar .flatpickr-current-month select.flatpickr-monthDropdown-months {
        margin-right: 8px !important;
    }
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper {
        width: 50px !important;
        overflow: visible !important;
        margin-left: 4px !important;
    }
    .flatpickr-calendar .flatpickr-current-month input.cur-year {
        font-size: 1rem !important;
        font-weight: 600 !important;
        width: 50px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
        -webkit-text-fill-color: #1e1e1e !important;
    }
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowUp,
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowDown {
        display: none !important;
    }
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
        background: #000;
        border-color: #000;
    }
    .flatpickr-day:hover {
        background: #f0f0f0;
    }
    /* Pickup date validation error */
    .tsb-pickup-validation-error {
        color: #cc1818;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .tsb-pickup-validation-error p {
        margin: 0;
    }
    </style>
    <script>
    (function() {
        var currentDateInput = null;
        var lastInitializedId = null;

        // Store the pickup date value globally so we can access it during checkout
        window.tsbPickupDateValue = '';

        function updateWooCommerceStore(fieldId, value) {
            // Store value globally
            window.tsbPickupDateValue = value;

            // Method 1: Set cookie directly in browser (most reliable)
            var expires = new Date();
            expires.setTime(expires.getTime() + (1 * 60 * 60 * 1000)); // 1 hour
            document.cookie = 'tsb_pickup_date=' + encodeURIComponent(value) + ';expires=' + expires.toUTCString() + ';path=/';
            console.log('Pickup date saved to cookie:', value);

            // Method 2: Save via AJAX (backup)
            var formData = new FormData();
            formData.append('action', 'tsb_save_pickup_date');
            formData.append('pickup_date', value);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    console.log('Pickup date saved via AJAX:', data.data.date);
                } else {
                    console.log('Failed to save pickup date:', data);
                }
            })
            .catch(function(error) {
                console.log('AJAX error:', error);
            });

            // Method 3: Try WooCommerce Blocks store
            if (window.wp && window.wp.data) {
                try {
                    var dispatch = window.wp.data.dispatch;
                    var checkoutStore = dispatch('wc/store/checkout');
                    if (checkoutStore && typeof checkoutStore.setExtensionData === 'function') {
                        checkoutStore.setExtensionData('theskybakery', { 'pickup-date': value });
                        console.log('Used setExtensionData:', fieldId, value);
                    }
                } catch(e) {
                    console.log('Could not update WC store:', e);
                }
            }
        }

        // Intercept fetch to add pickup date to checkout API request
        function setupFetchIntercept() {
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                // Check if this is a checkout API request
                if (url && typeof url === 'string' && url.includes('/wc/store/v1/checkout')) {
                    console.log('Intercepted checkout API request');

                    if (options && options.body && window.tsbPickupDateValue) {
                        try {
                            var body = JSON.parse(options.body);

                            // Add to extensions
                            if (!body.extensions) body.extensions = {};
                            if (!body.extensions.theskybakery) body.extensions.theskybakery = {};
                            body.extensions.theskybakery['pickup-date'] = window.tsbPickupDateValue;

                            // Also add to additional_fields if it exists
                            if (!body.additional_fields) body.additional_fields = {};
                            body.additional_fields['theskybakery/pickup-date'] = window.tsbPickupDateValue;

                            options.body = JSON.stringify(body);
                            console.log('Added pickup date to checkout request:', window.tsbPickupDateValue);
                            console.log('Request body:', options.body);
                        } catch(e) {
                            console.log('Could not modify checkout request:', e);
                        }
                    }
                }
                return originalFetch.apply(this, arguments);
            };
            console.log('TSB fetch intercept registered');
        }

        // Setup fetch intercept on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFetchIntercept);
        } else {
            setupFetchIntercept();
        }

        // Show validation error in WooCommerce style
        function showValidationError(message) {
            // Remove existing error
            var existingError = document.querySelector('.tsb-pickup-validation-error');
            if (existingError) {
                existingError.remove();
            }

            // Find pickup date field wrapper
            var pickupField = document.querySelector('.wc-block-components-text-input[class*="pickup-date"]');
            if (!pickupField) {
                pickupField = document.querySelector('[id*="pickup-date"]');
                if (pickupField) {
                    pickupField = pickupField.closest('.wc-block-components-text-input');
                }
            }

            // Create error element
            var errorDiv = document.createElement('div');
            errorDiv.className = 'tsb-pickup-validation-error wc-block-components-validation-error';
            errorDiv.setAttribute('role', 'alert');
            errorDiv.innerHTML = '<p>' + message + '</p>';

            // Insert error after the field
            if (pickupField) {
                pickupField.parentNode.insertBefore(errorDiv, pickupField.nextSibling);
                pickupField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Fallback: show at top of checkout
                var checkoutForm = document.querySelector('.wc-block-checkout__form');
                if (checkoutForm) {
                    checkoutForm.insertBefore(errorDiv, checkoutForm.firstChild);
                }
            }
        }

        // Clear validation error
        function clearValidationError() {
            var existingError = document.querySelector('.tsb-pickup-validation-error');
            if (existingError) {
                existingError.remove();
            }
        }

        // Validate pickup date
        function validatePickupDate() {
            clearValidationError();

            if (!window.tsbPickupDateValue) {
                showValidationError('<?php _e("Please select a pickup date.", "theskybakery"); ?>');
                return false;
            }

            var selectedDate = new Date(window.tsbPickupDateValue);
            var minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            minDate.setHours(0, 0, 0, 0);

            if (selectedDate < minDate) {
                showValidationError('<?php _e("Pickup date must be at least 3 days from now.", "theskybakery"); ?>');
                return false;
            }

            return true;
        }

        // Client-side validation for pickup date
        function setupValidation() {
            // Intercept Place Order button click
            document.addEventListener('click', function(e) {
                var button = e.target.closest('.wc-block-components-checkout-place-order-button');
                if (button) {
                    if (!validatePickupDate()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }
            }, true);

            console.log('TSB pickup date validation setup complete');
        }

        // Setup validation
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(setupValidation, 1000);
            });
        } else {
            setTimeout(setupValidation, 1000);
        }

        function initFlatpickr() {
            if (typeof flatpickr === 'undefined') {
                setTimeout(initFlatpickr, 100);
                return;
            }

            // Find the pickup date input field
            var dateInput = document.querySelector('input[id*="pickup-date"]');
            if (!dateInput) {
                dateInput = document.querySelector('input[name*="pickup-date"]');
            }

            if (!dateInput) {
                return;
            }

            // Get unique identifier for this input
            var inputId = dateInput.id || dateInput.name || 'pickup-date';

            // Skip if already initialized on this exact element
            if (dateInput._flatpickr) {
                return;
            }

            // Destroy old instance if input element changed
            if (currentDateInput && currentDateInput !== dateInput && currentDateInput._flatpickr) {
                currentDateInput._flatpickr.destroy();
            }

            currentDateInput = dateInput;
            lastInitializedId = inputId;
            var wrapper = dateInput.closest('.wc-block-components-text-input');

            // Log input info for debugging
            console.log('Flatpickr init on input:', dateInput.id, dateInput.name, dateInput);

            var fp = flatpickr(dateInput, {
                minDate: '<?php echo $min_date; ?>',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'D, M j, Y',
                disableMobile: true,
                onReady: function(selectedDates, dateStr, instance) {
                    if (instance.altInput && wrapper) {
                        instance.altInput.className = dateInput.className;
                        instance.altInput.placeholder = '<?php _e("Select a pickup date", "theskybakery"); ?>';
                        wrapper.classList.add('tsb-flatpickr-wrapper');
                        wrapper.classList.add('is-active');
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (!dateStr) return;

                    // Add class to wrapper for label positioning
                    if (wrapper) {
                        wrapper.classList.add('is-active');
                        wrapper.classList.add('has-date-value');
                    }

                    // Store value globally
                    window.tsbPickupDateValue = dateStr;

                    // Set value using native setter
                    var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                    nativeInputValueSetter.call(dateInput, dateStr);

                    // Dispatch events
                    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    dateInput.dispatchEvent(new Event('blur', { bubbles: true }));

                    // Try WooCommerce store
                    updateWooCommerceStore('theskybakery/pickup-date', dateStr);

                    console.log('Pickup date set to:', dateStr, 'Input value:', dateInput.value, 'Global:', window.tsbPickupDateValue);
                },
                onClose: function(selectedDates, dateStr, instance) {
                    // Trigger blur when calendar closes
                    if (dateStr) {
                        dateInput.dispatchEvent(new Event('blur', { bubbles: true }));
                        dateInput.dispatchEvent(new FocusEvent('focusout', { bubbles: true }));
                    }
                }
            });
        }

        // Run on page load with delays for dynamic content
        function scheduleInit() {
            setTimeout(initFlatpickr, 300);
            setTimeout(initFlatpickr, 800);
            setTimeout(initFlatpickr, 1500);
            setTimeout(initFlatpickr, 3000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleInit);
        } else {
            scheduleInit();
        }

        // Observe DOM changes for dynamic loading (React re-renders)
        var observer = new MutationObserver(function(mutations) {
            // Check if our input still exists or was replaced
            var dateInput = document.querySelector('input[id*="pickup-date"]');
            if (dateInput && !dateInput._flatpickr) {
                clearTimeout(window.tsbFlatpickrTimeout);
                window.tsbFlatpickrTimeout = setTimeout(initFlatpickr, 150);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'tsb_checkout_pickup_date_script', 999);
