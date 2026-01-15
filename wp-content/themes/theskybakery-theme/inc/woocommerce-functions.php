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
    
    $start = 7; // 7 AM
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
