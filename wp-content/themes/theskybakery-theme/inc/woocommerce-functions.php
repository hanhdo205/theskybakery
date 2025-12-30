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
 * Disable default WooCommerce styles
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

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
