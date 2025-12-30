<?php
/**
 * Custom Post Types
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Store Location Custom Post Type
 */
function tsb_register_store_location_cpt() {
    $labels = array(
        'name'               => __('Store Locations', 'theskybakery'),
        'singular_name'      => __('Store Location', 'theskybakery'),
        'menu_name'          => __('Store Locations', 'theskybakery'),
        'add_new'            => __('Add New', 'theskybakery'),
        'add_new_item'       => __('Add New Store', 'theskybakery'),
        'edit_item'          => __('Edit Store', 'theskybakery'),
        'new_item'           => __('New Store', 'theskybakery'),
        'view_item'          => __('View Store', 'theskybakery'),
        'search_items'       => __('Search Stores', 'theskybakery'),
        'not_found'          => __('No stores found', 'theskybakery'),
        'not_found_in_trash' => __('No stores found in trash', 'theskybakery'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'store-location'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-store',
        'supports'           => array('title', 'editor', 'thumbnail'),
        'show_in_rest'       => true,
    );

    register_post_type('store_location', $args);
}
add_action('init', 'tsb_register_store_location_cpt');

/**
 * Register Slider Custom Post Type
 */
function tsb_register_slider_cpt() {
    $labels = array(
        'name'               => __('Sliders', 'theskybakery'),
        'singular_name'      => __('Slider', 'theskybakery'),
        'menu_name'          => __('Sliders', 'theskybakery'),
        'add_new'            => __('Add New', 'theskybakery'),
        'add_new_item'       => __('Add New Slide', 'theskybakery'),
        'edit_item'          => __('Edit Slide', 'theskybakery'),
        'new_item'           => __('New Slide', 'theskybakery'),
        'view_item'          => __('View Slide', 'theskybakery'),
        'search_items'       => __('Search Slides', 'theskybakery'),
        'not_found'          => __('No slides found', 'theskybakery'),
        'not_found_in_trash' => __('No slides found in trash', 'theskybakery'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 26,
        'menu_icon'          => 'dashicons-slides',
        'supports'           => array('title', 'thumbnail'),
        'show_in_rest'       => true,
    );

    register_post_type('tsb_slider', $args);
}
add_action('init', 'tsb_register_slider_cpt');

/**
 * Add Store Location Meta Boxes
 */
function tsb_add_store_meta_boxes() {
    add_meta_box(
        'tsb_store_details',
        __('Store Details', 'theskybakery'),
        'tsb_store_details_callback',
        'store_location',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tsb_add_store_meta_boxes');

/**
 * Store Details Meta Box Callback
 */
function tsb_store_details_callback($post) {
    wp_nonce_field('tsb_store_meta', 'tsb_store_meta_nonce');

    $address = get_post_meta($post->ID, '_store_address', true);
    $phone = get_post_meta($post->ID, '_store_phone', true);
    $email = get_post_meta($post->ID, '_store_email', true);
    $hours = get_post_meta($post->ID, '_store_hours', true);
    $map_embed = get_post_meta($post->ID, '_store_map_embed', true);
    $lat = get_post_meta($post->ID, '_store_lat', true);
    $lng = get_post_meta($post->ID, '_store_lng', true);
    ?>
    <style>
        .tsb-meta-row {
            margin-bottom: 15px;
        }
        .tsb-meta-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .tsb-meta-row input[type="text"],
        .tsb-meta-row input[type="email"],
        .tsb-meta-row textarea {
            width: 100%;
        }
        .tsb-meta-row textarea {
            min-height: 100px;
        }
    </style>
    <div class="tsb-meta-row">
        <label for="store_address"><?php _e('Address', 'theskybakery'); ?></label>
        <input type="text" id="store_address" name="store_address" value="<?php echo esc_attr($address); ?>">
    </div>

    <div class="tsb-meta-row">
        <label for="store_phone"><?php _e('Phone', 'theskybakery'); ?></label>
        <input type="text" id="store_phone" name="store_phone" value="<?php echo esc_attr($phone); ?>">
    </div>

    <div class="tsb-meta-row">
        <label for="store_email"><?php _e('Email', 'theskybakery'); ?></label>
        <input type="email" id="store_email" name="store_email" value="<?php echo esc_attr($email); ?>">
    </div>

    <div class="tsb-meta-row">
        <label for="store_hours"><?php _e('Opening Hours', 'theskybakery'); ?></label>
        <textarea id="store_hours" name="store_hours"><?php echo esc_textarea($hours); ?></textarea>
        <p class="description"><?php _e('Enter each line separately (e.g., Mon-Fri: 7am-5pm)', 'theskybakery'); ?></p>
    </div>

    <div class="tsb-meta-row">
        <label for="store_map_embed"><?php _e('Google Maps Embed Code', 'theskybakery'); ?></label>
        <textarea id="store_map_embed" name="store_map_embed"><?php echo esc_textarea($map_embed); ?></textarea>
        <p class="description"><?php _e('Paste the Google Maps iframe embed code here', 'theskybakery'); ?></p>
    </div>

    <div class="tsb-meta-row">
        <label for="store_lat"><?php _e('Latitude', 'theskybakery'); ?></label>
        <input type="text" id="store_lat" name="store_lat" value="<?php echo esc_attr($lat); ?>">
    </div>

    <div class="tsb-meta-row">
        <label for="store_lng"><?php _e('Longitude', 'theskybakery'); ?></label>
        <input type="text" id="store_lng" name="store_lng" value="<?php echo esc_attr($lng); ?>">
    </div>
    <?php
}

/**
 * Save Store Meta
 */
function tsb_save_store_meta($post_id) {
    if (!isset($_POST['tsb_store_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['tsb_store_meta_nonce'], 'tsb_store_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'store_address' => '_store_address',
        'store_phone'   => '_store_phone',
        'store_email'   => '_store_email',
        'store_hours'   => '_store_hours',
        'store_map_embed' => '_store_map_embed',
        'store_lat'     => '_store_lat',
        'store_lng'     => '_store_lng',
    );

    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            if ($field === 'store_map_embed') {
                update_post_meta($post_id, $meta_key, wp_kses_post($_POST[$field]));
            } else {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
add_action('save_post_store_location', 'tsb_save_store_meta');

/**
 * Add Slider Meta Box
 */
function tsb_add_slider_meta_boxes() {
    add_meta_box(
        'tsb_slider_details',
        __('Slide Details', 'theskybakery'),
        'tsb_slider_details_callback',
        'tsb_slider',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tsb_add_slider_meta_boxes');

/**
 * Slider Details Meta Box Callback
 */
function tsb_slider_details_callback($post) {
    wp_nonce_field('tsb_slider_meta', 'tsb_slider_meta_nonce');

    $link = get_post_meta($post->ID, '_slide_link', true);
    $order = get_post_meta($post->ID, '_slide_order', true);
    ?>
    <div class="tsb-meta-row">
        <label for="slide_link"><?php _e('Link URL', 'theskybakery'); ?></label>
        <input type="text" id="slide_link" name="slide_link" value="<?php echo esc_url($link); ?>" style="width: 100%;">
    </div>

    <div class="tsb-meta-row">
        <label for="slide_order"><?php _e('Order', 'theskybakery'); ?></label>
        <input type="number" id="slide_order" name="slide_order" value="<?php echo esc_attr($order); ?>" style="width: 100px;">
    </div>
    <?php
}

/**
 * Save Slider Meta
 */
function tsb_save_slider_meta($post_id) {
    if (!isset($_POST['tsb_slider_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['tsb_slider_meta_nonce'], 'tsb_slider_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['slide_link'])) {
        update_post_meta($post_id, '_slide_link', esc_url_raw($_POST['slide_link']));
    }

    if (isset($_POST['slide_order'])) {
        update_post_meta($post_id, '_slide_order', absint($_POST['slide_order']));
    }
}
add_action('save_post_tsb_slider', 'tsb_save_slider_meta');
