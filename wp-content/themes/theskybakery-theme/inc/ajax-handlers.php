<?php
/**
 * AJAX Handlers
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Add to Cart
 */
function tsb_ajax_add_to_cart() {
    check_ajax_referer('tsb_nonce', 'nonce');

    $product_id = absint($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

    if (!$product_id) {
        wp_send_json_error(array('message' => __('Invalid product.', 'theskybakery')));
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error(array('message' => __('Product not found.', 'theskybakery')));
    }

    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);

    if ($cart_item_key) {
        wp_send_json_success(array(
            'message'    => __('Product added to cart.', 'theskybakery'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to add product to cart.', 'theskybakery')));
    }
}
add_action('wp_ajax_tsb_add_to_cart', 'tsb_ajax_add_to_cart');
add_action('wp_ajax_nopriv_tsb_add_to_cart', 'tsb_ajax_add_to_cart');

/**
 * AJAX Get Cart Count
 */
function tsb_ajax_get_cart_count() {
    check_ajax_referer('tsb_nonce', 'nonce');

    wp_send_json_success(array(
        'count' => WC()->cart->get_cart_contents_count(),
    ));
}
add_action('wp_ajax_tsb_get_cart_count', 'tsb_ajax_get_cart_count');
add_action('wp_ajax_nopriv_tsb_get_cart_count', 'tsb_ajax_get_cart_count');

/**
 * AJAX Newsletter Subscribe
 */
function tsb_ajax_newsletter_subscribe() {
    check_ajax_referer('tsb_nonce', 'nonce');

    $email = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Please enter a valid email address.', 'theskybakery')));
    }

    // Store subscriber
    $subscribers = get_option('tsb_newsletter_subscribers', array());
    
    if (in_array($email, $subscribers)) {
        wp_send_json_error(array('message' => __('This email is already subscribed.', 'theskybakery')));
    }

    $subscribers[] = $email;
    update_option('tsb_newsletter_subscribers', $subscribers);

    // Send notification email
    $admin_email = get_option('admin_email');
    $subject = __('New Newsletter Subscriber', 'theskybakery');
    $message = sprintf(__('New subscriber: %s', 'theskybakery'), $email);
    wp_mail($admin_email, $subject, $message);

    wp_send_json_success(array('message' => __('Thank you for subscribing!', 'theskybakery')));
}
add_action('wp_ajax_tsb_newsletter_subscribe', 'tsb_ajax_newsletter_subscribe');
add_action('wp_ajax_nopriv_tsb_newsletter_subscribe', 'tsb_ajax_newsletter_subscribe');

/**
 * AJAX Get Pickup Times
 */
function tsb_ajax_get_pickup_times() {
    check_ajax_referer('tsb_nonce', 'nonce');

    $date = sanitize_text_field($_POST['date']);
    $store_id = absint($_POST['store']);

    if (!$date || !$store_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'theskybakery')));
    }

    // Get store hours
    $store_hours = get_post_meta($store_id, '_store_hours', true);

    // Generate available time slots
    $times = array();
    $day_of_week = date('N', strtotime($date)); // 1 = Monday, 7 = Sunday

    // Default hours: 7am - 5pm
    $start_hour = 7;
    $end_hour = 17;

    // Weekend hours might be different
    if ($day_of_week == 6) { // Saturday
        $start_hour = 8;
        $end_hour = 16;
    } elseif ($day_of_week == 7) { // Sunday
        $start_hour = 9;
        $end_hour = 15;
    }

    // Generate 30-minute slots
    for ($hour = $start_hour; $hour < $end_hour; $hour++) {
        for ($min = 0; $min < 60; $min += 30) {
            $time = sprintf('%02d:%02d', $hour, $min);
            $times[] = array(
                'value' => $time,
                'label' => date('g:i A', strtotime($time)),
            );
        }
    }

    // Add last slot
    $times[] = array(
        'value' => sprintf('%02d:00', $end_hour),
        'label' => date('g:i A', strtotime(sprintf('%02d:00', $end_hour))),
    );

    wp_send_json_success(array('times' => $times));
}
add_action('wp_ajax_tsb_get_pickup_times', 'tsb_ajax_get_pickup_times');
add_action('wp_ajax_nopriv_tsb_get_pickup_times', 'tsb_ajax_get_pickup_times');

/**
 * AJAX Add Custom Cake
 */
function tsb_ajax_add_custom_cake() {
    check_ajax_referer('tsb_nonce', 'nonce');

    $cake_data = isset($_POST['cake_data']) ? $_POST['cake_data'] : array();

    if (empty($cake_data['size']) || empty($cake_data['flavor'])) {
        wp_send_json_error(array('message' => __('Please select cake size and flavor.', 'theskybakery')));
    }

    // Calculate price
    $price = tsb_calculate_cake_price($cake_data);

    // Create cart item data
    $cart_item_data = array(
        'custom_cake'   => true,
        'cake_size'     => sanitize_text_field($cake_data['size']),
        'cake_flavor'   => sanitize_text_field($cake_data['flavor']),
        'cake_frosting' => sanitize_text_field($cake_data['frosting'] ?? 'buttercream'),
        'cake_toppings' => array_map('sanitize_text_field', (array) ($cake_data['toppings'] ?? array())),
        'cake_message'  => sanitize_text_field($cake_data['message'] ?? ''),
        'pickup_store'  => absint($cake_data['pickup_store'] ?? 0),
        'pickup_date'   => sanitize_text_field($cake_data['pickup_date'] ?? ''),
        'pickup_time'   => sanitize_text_field($cake_data['pickup_time'] ?? ''),
        'custom_price'  => $price,
    );

    // Get or create custom cake product
    $custom_cake_product_id = tsb_get_custom_cake_product();

    if (!$custom_cake_product_id) {
        wp_send_json_error(array('message' => __('Custom cake product not found.', 'theskybakery')));
    }

    // Add to cart
    $cart_item_key = WC()->cart->add_to_cart($custom_cake_product_id, 1, 0, array(), $cart_item_data);

    if ($cart_item_key) {
        wp_send_json_success(array(
            'message'    => __('Custom cake added to cart!', 'theskybakery'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'redirect'   => wc_get_cart_url(),
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to add cake to cart.', 'theskybakery')));
    }
}
add_action('wp_ajax_tsb_add_custom_cake', 'tsb_ajax_add_custom_cake');
add_action('wp_ajax_nopriv_tsb_add_custom_cake', 'tsb_ajax_add_custom_cake');

/**
 * Calculate cake price
 */
function tsb_calculate_cake_price($cake_data) {
    $price = 0;

    // Size prices
    $size_prices = array(
        'small'  => 38,
        'medium' => 55,
        'large'  => 75,
        'xl'     => 95,
    );

    // Flavor prices
    $flavor_prices = array(
        'vanilla'    => 0,
        'chocolate'  => 0,
        'red-velvet' => 5,
        'carrot'     => 5,
        'lemon'      => 3,
        'marble'     => 3,
        'mud'        => 5,
    );

    // Frosting prices
    $frosting_prices = array(
        'buttercream'       => 0,
        'cream-cheese'      => 5,
        'chocolate-ganache' => 5,
        'whipped-cream'     => 0,
        'fondant'           => 15,
    );

    // Topping prices
    $topping_prices = array(
        'fresh-fruits'   => 8,
        'chocolate-drip' => 5,
        'sprinkles'      => 3,
        'macarons'       => 10,
        'gold-leaf'      => 12,
        'flowers'        => 8,
        'edible-image'   => 15,
    );

    // Calculate
    $price += $size_prices[$cake_data['size']] ?? 0;
    $price += $flavor_prices[$cake_data['flavor']] ?? 0;
    $price += $frosting_prices[$cake_data['frosting'] ?? 'buttercream'] ?? 0;

    if (!empty($cake_data['toppings'])) {
        foreach ((array) $cake_data['toppings'] as $topping) {
            $price += $topping_prices[$topping] ?? 0;
        }
    }

    return $price;
}

/**
 * Get or create custom cake product
 */
function tsb_get_custom_cake_product() {
    $product_id = get_option('tsb_custom_cake_product_id');

    if ($product_id && get_post($product_id)) {
        return $product_id;
    }

    // Create product
    $product = new WC_Product_Simple();
    $product->set_name('Custom Cake');
    $product->set_status('private');
    $product->set_catalog_visibility('hidden');
    $product->set_price(0);
    $product->set_regular_price(0);
    $product->set_sold_individually(false);
    $product->set_virtual(false);
    $product_id = $product->save();

    update_option('tsb_custom_cake_product_id', $product_id);

    return $product_id;
}

/**
 * Set custom cake price in cart
 */
function tsb_set_custom_cake_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_cake']) && $cart_item['custom_cake']) {
            $price = floatval($cart_item['custom_price'] ?? 0);
            $cart_item['data']->set_price($price);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'tsb_set_custom_cake_price', 20);

/**
 * Display custom cake details in cart
 */
function tsb_display_custom_cake_in_cart($item_data, $cart_item) {
    if (isset($cart_item['custom_cake']) && $cart_item['custom_cake']) {
        $item_data[] = array(
            'key'   => __('Size', 'theskybakery'),
            'value' => ucfirst($cart_item['cake_size']),
        );
        $item_data[] = array(
            'key'   => __('Flavor', 'theskybakery'),
            'value' => ucfirst(str_replace('-', ' ', $cart_item['cake_flavor'])),
        );
        $item_data[] = array(
            'key'   => __('Frosting', 'theskybakery'),
            'value' => ucfirst(str_replace('-', ' ', $cart_item['cake_frosting'])),
        );

        if (!empty($cart_item['cake_toppings'])) {
            $toppings = array_map(function($t) {
                return ucfirst(str_replace('-', ' ', $t));
            }, $cart_item['cake_toppings']);
            $item_data[] = array(
                'key'   => __('Toppings', 'theskybakery'),
                'value' => implode(', ', $toppings),
            );
        }

        if (!empty($cart_item['cake_message'])) {
            $item_data[] = array(
                'key'   => __('Message', 'theskybakery'),
                'value' => $cart_item['cake_message'],
            );
        }

        if ($cart_item['pickup_store']) {
            $store = get_post($cart_item['pickup_store']);
            if ($store) {
                $item_data[] = array(
                    'key'   => __('Pickup Location', 'theskybakery'),
                    'value' => $store->post_title,
                );
            }
        }

        if (!empty($cart_item['pickup_date'])) {
            $item_data[] = array(
                'key'   => __('Pickup Date', 'theskybakery'),
                'value' => date('F j, Y', strtotime($cart_item['pickup_date'])),
            );
        }

        if (!empty($cart_item['pickup_time'])) {
            $item_data[] = array(
                'key'   => __('Pickup Time', 'theskybakery'),
                'value' => date('g:i A', strtotime($cart_item['pickup_time'])),
            );
        }
    }

    return $item_data;
}
add_filter('woocommerce_get_item_data', 'tsb_display_custom_cake_in_cart', 10, 2);

/**
 * Save custom cake data to order
 */
function tsb_save_custom_cake_order_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_cake']) && $values['custom_cake']) {
        $item->add_meta_data(__('Size', 'theskybakery'), ucfirst($values['cake_size']));
        $item->add_meta_data(__('Flavor', 'theskybakery'), ucfirst(str_replace('-', ' ', $values['cake_flavor'])));
        $item->add_meta_data(__('Frosting', 'theskybakery'), ucfirst(str_replace('-', ' ', $values['cake_frosting'])));

        if (!empty($values['cake_toppings'])) {
            $toppings = array_map(function($t) {
                return ucfirst(str_replace('-', ' ', $t));
            }, $values['cake_toppings']);
            $item->add_meta_data(__('Toppings', 'theskybakery'), implode(', ', $toppings));
        }

        if (!empty($values['cake_message'])) {
            $item->add_meta_data(__('Message', 'theskybakery'), $values['cake_message']);
        }
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'tsb_save_custom_cake_order_meta', 10, 4);
