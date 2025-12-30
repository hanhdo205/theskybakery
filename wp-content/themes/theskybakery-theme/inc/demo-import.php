<?php
/**
 * Demo Data Import
 *
 * Handles importing demo content for TheSkyBakery theme
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add demo import admin menu
 */
function tsb_add_demo_import_menu() {
    add_theme_page(
        'Demo Import',
        'Demo Import',
        'manage_options',
        'tsb-demo-import',
        'tsb_demo_import_page'
    );
}
add_action('admin_menu', 'tsb_add_demo_import_menu');

/**
 * Demo import admin page
 */
function tsb_demo_import_page() {
    ?>
    <div class="wrap">
        <h1>TheSkyBakery Demo Import</h1>
        
        <?php if (isset($_GET['imported'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Demo data imported successfully!</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])) : ?>
            <div class="notice notice-error is-dismissible">
                <p>Error importing demo data. Please try again.</p>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 600px; padding: 20px;">
            <h2>Import Demo Content</h2>
            <p>This will import the following demo content:</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>5 Store Locations</li>
                <li>15 Sample Products with Images</li>
                <li>3 Homepage Slider Images</li>
                <li>Sample Pages (Menu, Stores, Cake Builder)</li>
                <li>Navigation Menus</li>
                <li>Theme Options & Settings</li>
            </ul>
            
            <p><strong>Note:</strong> Make sure WooCommerce is installed and activated before importing.</p>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('tsb_demo_import', 'tsb_demo_nonce'); ?>
                <input type="hidden" name="action" value="tsb_import_demo">
                
                <p>
                    <label>
                        <input type="checkbox" name="import_stores" value="1" checked>
                        Import Store Locations
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="import_products" value="1" checked>
                        Import Sample Products (with images)
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="import_sliders" value="1" checked>
                        Import Slider Images
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="import_pages" value="1" checked>
                        Import Pages
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="import_menus" value="1" checked>
                        Import Navigation Menus
                    </label>
                </p>
                
                <p style="margin-top: 20px;">
                    <input type="submit" class="button button-primary button-hero" value="Import Demo Data">
                </p>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Handle demo import
 */
function tsb_handle_demo_import() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    if (!wp_verify_nonce($_POST['tsb_demo_nonce'], 'tsb_demo_import')) {
        wp_die('Security check failed');
    }

    $imported = true;

    // Import stores
    if (!empty($_POST['import_stores'])) {
        $imported = $imported && tsb_import_stores();
    }

    // Import products
    if (!empty($_POST['import_products'])) {
        $imported = $imported && tsb_import_products();
    }

    // Import sliders
    if (!empty($_POST['import_sliders'])) {
        $imported = $imported && tsb_import_sliders();
    }

    // Import pages
    if (!empty($_POST['import_pages'])) {
        $imported = $imported && tsb_import_pages();
    }

    // Import menus
    if (!empty($_POST['import_menus'])) {
        $imported = $imported && tsb_import_menus();
    }

    // Set theme options
    tsb_set_theme_options();

    $redirect = admin_url('themes.php?page=tsb-demo-import');
    $redirect = add_query_arg($imported ? 'imported' : 'error', '1', $redirect);
    
    wp_redirect($redirect);
    exit;
}
add_action('admin_post_tsb_import_demo', 'tsb_handle_demo_import');

/**
 * Upload image from theme folder to media library
 */
function tsb_upload_image($image_path, $post_id = 0) {
    // Check if file exists
    $full_path = get_template_directory() . $image_path;
    
    if (!file_exists($full_path)) {
        return false;
    }

    // Get file info
    $filename = basename($full_path);
    $filetype = wp_check_filetype($filename);
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    
    // Copy file to uploads
    $new_file = $upload_dir['path'] . '/' . $filename;
    
    if (!copy($full_path, $new_file)) {
        return false;
    }

    // Prepare attachment data
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . $filename,
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert attachment
    $attach_id = wp_insert_attachment($attachment, $new_file, $post_id);

    if (!$attach_id) {
        return false;
    }

    // Generate metadata
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $new_file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

/**
 * Import store locations
 */
function tsb_import_stores() {
    $stores = array(
        array(
            'title' => 'Lakelands Shopping Centre',
            'address' => '1 Miltona Drive, Lakelands WA 6180',
            'phone' => '(08) 9534 5501',
            'email' => 'lakelands@theskybakery.com.au',
            'hours' => "Monday - Friday: 7:00 AM - 5:00 PM\nSaturday: 8:00 AM - 4:00 PM\nSunday: 9:00 AM - 3:00 PM",
            'lat' => '-32.4671',
            'lng' => '115.7652',
        ),
        array(
            'title' => 'Mandurah Forum',
            'address' => 'Shop 47, Mandurah Forum, 330 Pinjarra Road, Mandurah WA 6210',
            'phone' => '(08) 9535 2268',
            'email' => 'forum@theskybakery.com.au',
            'hours' => "Monday - Friday: 7:00 AM - 5:30 PM\nSaturday: 8:00 AM - 5:00 PM\nSunday: 9:00 AM - 4:00 PM",
            'lat' => '-32.5269',
            'lng' => '115.7443',
        ),
        array(
            'title' => 'Rockingham Centre',
            'address' => 'Shop 1086, Rockingham Centre, 1 Council Avenue, Rockingham WA 6168',
            'phone' => '(08) 9528 2898',
            'email' => 'rockingham@theskybakery.com.au',
            'hours' => "Monday - Friday: 7:00 AM - 5:30 PM\nSaturday: 8:00 AM - 5:00 PM\nSunday: 9:00 AM - 4:00 PM",
            'lat' => '-32.2772',
            'lng' => '115.7294',
        ),
        array(
            'title' => 'Kwinana',
            'address' => 'Shop 18, Kwinana Hub, 4 Chisham Avenue, Kwinana Town Centre WA 6167',
            'phone' => '(08) 9419 5335',
            'email' => 'kwinana@theskybakery.com.au',
            'hours' => "Monday - Friday: 7:00 AM - 5:00 PM\nSaturday: 8:00 AM - 4:00 PM\nSunday: 9:00 AM - 3:00 PM",
            'lat' => '-32.2410',
            'lng' => '115.7706',
        ),
        array(
            'title' => 'Warnbro',
            'address' => 'Shop 11, Warnbro Centre, 206 Warnbro Sound Avenue, Warnbro WA 6169',
            'phone' => '(08) 9593 2255',
            'email' => 'warnbro@theskybakery.com.au',
            'hours' => "Monday - Friday: 7:00 AM - 5:00 PM\nSaturday: 8:00 AM - 4:00 PM\nSunday: 9:00 AM - 3:00 PM",
            'lat' => '-32.3394',
            'lng' => '115.7520',
        ),
    );

    foreach ($stores as $store) {
        // Check if store already exists
        $existing = get_posts(array(
            'post_type' => 'store_location',
            'title' => $store['title'],
            'post_status' => 'publish',
            'numberposts' => 1,
        ));

        if (!empty($existing)) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title' => $store['title'],
            'post_type' => 'store_location',
            'post_status' => 'publish',
        ));

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_store_address', $store['address']);
            update_post_meta($post_id, '_store_phone', $store['phone']);
            update_post_meta($post_id, '_store_email', $store['email']);
            update_post_meta($post_id, '_store_hours', $store['hours']);
            update_post_meta($post_id, '_store_lat', $store['lat']);
            update_post_meta($post_id, '_store_lng', $store['lng']);
            
            // Upload store image
            $image_id = tsb_upload_image('/assets/images/store-placeholder.jpg', $post_id);
            if ($image_id) {
                set_post_thumbnail($post_id, $image_id);
            }
        }
    }

    return true;
}

/**
 * Import sample products
 */
function tsb_import_products() {
    if (!class_exists('WooCommerce')) {
        return false;
    }

    // Create product categories
    $categories = array(
        'Birthday' => array(
            'Black Forrest',
            'Carrot Cake',
            'Chocolate Gateau',
            'Coffee Cake',
            'Colourful Cake',
            'Drip Cake',
            'Mud Cake',
            'Rosie Cake',
            'Torte',
        ),
        'Catering' => array(),
        'Cream Cake' => array(),
        'Cup Cake' => array(),
        'Edible Picture' => array(),
        'Others' => array(),
    );

    $category_ids = array();
    
    foreach ($categories as $parent => $children) {
        $parent_term = term_exists($parent, 'product_cat');
        if (!$parent_term) {
            $parent_term = wp_insert_term($parent, 'product_cat');
        }
        
        if (!is_wp_error($parent_term)) {
            $parent_id = is_array($parent_term) ? $parent_term['term_id'] : $parent_term;
            $category_ids[$parent] = $parent_id;
            
            foreach ($children as $child) {
                $child_term = term_exists($child, 'product_cat');
                if (!$child_term) {
                    wp_insert_term($child, 'product_cat', array('parent' => $parent_id));
                }
            }
        }
    }

    // Sample products with images
    $products = array(
        array(
            'name' => 'Classic Chocolate Mud Cake',
            'price' => '45.00',
            'sale_price' => '',
            'category' => 'Birthday',
            'description' => 'Rich, dense, and utterly chocolatey. Our signature mud cake is a chocolate lover\'s dream.',
            'image' => '/assets/images/products/chocolate-mud-cake.jpg',
        ),
        array(
            'name' => 'Red Velvet Dream',
            'price' => '55.00',
            'sale_price' => '48.00',
            'category' => 'Birthday',
            'description' => 'Velvety smooth with a hint of cocoa, topped with cream cheese frosting.',
            'image' => '/assets/images/products/red-velvet.jpg',
        ),
        array(
            'name' => 'Carrot Cake Supreme',
            'price' => '42.00',
            'sale_price' => '',
            'category' => 'Birthday',
            'description' => 'Moist carrot cake with walnuts and cream cheese frosting.',
            'image' => '/assets/images/products/carrot-cake.jpg',
        ),
        array(
            'name' => 'Rainbow Drip Cake',
            'price' => '85.00',
            'sale_price' => '',
            'category' => 'Birthday',
            'description' => 'Colorful layers with chocolate drip and sprinkles. Perfect for celebrations!',
            'image' => '/assets/images/products/rainbow-drip.jpg',
        ),
        array(
            'name' => 'Black Forest Gateau',
            'price' => '65.00',
            'sale_price' => '',
            'category' => 'Birthday',
            'description' => 'Chocolate sponge with cherries and whipped cream.',
            'image' => '/assets/images/products/black-forest.jpg',
        ),
        array(
            'name' => 'Vanilla Bean Cupcakes (12 pack)',
            'price' => '36.00',
            'sale_price' => '32.00',
            'category' => 'Cup Cake',
            'description' => 'Light and fluffy vanilla cupcakes with buttercream frosting.',
            'image' => '/assets/images/products/vanilla-cupcakes.jpg',
        ),
        array(
            'name' => 'Chocolate Fudge Cupcakes (12 pack)',
            'price' => '38.00',
            'sale_price' => '',
            'category' => 'Cup Cake',
            'description' => 'Decadent chocolate cupcakes with rich fudge frosting.',
            'image' => '/assets/images/products/chocolate-cupcakes.jpg',
        ),
        array(
            'name' => 'Mixed Pastry Platter',
            'price' => '75.00',
            'sale_price' => '',
            'category' => 'Catering',
            'description' => 'Assorted pastries perfect for meetings and events. Serves 10-12.',
            'image' => '/assets/images/products/pastry-platter.jpg',
        ),
        array(
            'name' => 'Mini Cake Selection',
            'price' => '95.00',
            'sale_price' => '',
            'category' => 'Catering',
            'description' => 'Selection of mini cakes for catering. Serves 15-20.',
            'image' => '/assets/images/products/mini-cakes.jpg',
        ),
        array(
            'name' => 'Fresh Cream Sponge',
            'price' => '38.00',
            'sale_price' => '',
            'category' => 'Cream Cake',
            'description' => 'Light sponge with fresh cream and seasonal fruits.',
            'image' => '/assets/images/products/cream-sponge.jpg',
        ),
        array(
            'name' => 'Strawberry Cream Cake',
            'price' => '42.00',
            'sale_price' => '',
            'category' => 'Cream Cake',
            'description' => 'Fresh strawberries with whipped cream on vanilla sponge.',
            'image' => '/assets/images/products/strawberry-cream.jpg',
        ),
        array(
            'name' => 'Photo Cake - Rectangle',
            'price' => '55.00',
            'sale_price' => '',
            'category' => 'Edible Picture',
            'description' => 'Customizable photo cake. Send us your image!',
            'image' => '/assets/images/products/photo-cake-rect.jpg',
        ),
        array(
            'name' => 'Photo Cake - Round',
            'price' => '50.00',
            'sale_price' => '',
            'category' => 'Edible Picture',
            'description' => 'Round photo cake with edible image printing.',
            'image' => '/assets/images/products/photo-cake-round.jpg',
        ),
        array(
            'name' => 'Coffee & Walnut Torte',
            'price' => '48.00',
            'sale_price' => '',
            'category' => 'Birthday',
            'description' => 'Rich coffee sponge with walnut pieces and coffee buttercream.',
            'image' => '/assets/images/products/coffee-walnut.jpg',
        ),
        array(
            'name' => 'Lemon Drizzle Cake',
            'price' => '35.00',
            'sale_price' => '',
            'category' => 'Others',
            'description' => 'Zesty lemon cake with sweet lemon drizzle.',
            'image' => '/assets/images/products/lemon-drizzle.jpg',
        ),
    );

    foreach ($products as $product_data) {
        // Check if product exists
        $existing = get_posts(array(
            'post_type' => 'product',
            'title' => $product_data['name'],
            'post_status' => 'publish',
            'numberposts' => 1,
        ));

        if (!empty($existing)) {
            continue;
        }

        $product = new WC_Product_Simple();
        $product->set_name($product_data['name']);
        $product->set_description($product_data['description']);
        $product->set_short_description($product_data['description']);
        $product->set_regular_price($product_data['price']);
        
        if (!empty($product_data['sale_price'])) {
            $product->set_sale_price($product_data['sale_price']);
        }
        
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_stock_status('instock');
        
        $product_id = $product->save();

        // Set category
        if (isset($category_ids[$product_data['category']])) {
            wp_set_object_terms($product_id, array($category_ids[$product_data['category']]), 'product_cat');
        }

        // Upload and set product image
        if (!empty($product_data['image'])) {
            $image_id = tsb_upload_image($product_data['image'], $product_id);
            if ($image_id) {
                $product->set_image_id($image_id);
                $product->save();
            }
        }
    }

    return true;
}

/**
 * Import slider images
 */
function tsb_import_sliders() {
    $sliders = array(
        array(
            'title' => 'Welcome to TheSkyBakery',
            'link' => home_url('/menu/'),
            'order' => 1,
            'image' => '/assets/images/sliders/slide1.jpg',
        ),
        array(
            'title' => 'Custom Cakes for Every Occasion',
            'link' => home_url('/cake-builder/'),
            'order' => 2,
            'image' => '/assets/images/sliders/slide2.jpg',
        ),
        array(
            'title' => 'Fresh Baked Daily',
            'link' => home_url('/menu/'),
            'order' => 3,
            'image' => '/assets/images/sliders/slide3.jpg',
        ),
    );

    foreach ($sliders as $slider) {
        $existing = get_posts(array(
            'post_type' => 'slider',
            'title' => $slider['title'],
            'post_status' => 'publish',
            'numberposts' => 1,
        ));

        if (!empty($existing)) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title' => $slider['title'],
            'post_type' => 'slider',
            'post_status' => 'publish',
        ));

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_slide_link', $slider['link']);
            update_post_meta($post_id, '_slide_order', $slider['order']);
            
            // Upload and set slider image
            if (!empty($slider['image'])) {
                $image_id = tsb_upload_image($slider['image'], $post_id);
                if ($image_id) {
                    set_post_thumbnail($post_id, $image_id);
                }
            }
        }
    }

    return true;
}

/**
 * Import pages
 */
function tsb_import_pages() {
    $pages = array(
        array(
            'title' => 'Menu',
            'template' => 'templates/template-menu.php',
            'content' => '',
        ),
        array(
            'title' => 'Store Locations',
            'template' => 'templates/template-store.php',
            'content' => '',
        ),
        array(
            'title' => 'Cake Builder',
            'template' => 'templates/template-cake-builder.php',
            'content' => '',
        ),
        array(
            'title' => 'About Us',
            'template' => '',
            'content' => '<h2>Our Story</h2>
<p>TheSkyBakery has been serving the community with delicious cakes and pastries since 2010. What started as a small family bakery has grown into five locations across Western Australia.</p>
<h2>Our Promise</h2>
<p>We use only the finest ingredients, sourced locally where possible, to create memorable treats for every occasion. Every cake is baked fresh daily with love and attention to detail.</p>
<h2>Visit Us</h2>
<p>We have five convenient locations ready to serve you. Whether you\'re looking for a birthday cake, catering for an event, or just a sweet treat, we\'re here to help!</p>',
        ),
        array(
            'title' => 'Contact',
            'template' => '',
            'content' => '<h2>Get in Touch</h2>
<p>We\'d love to hear from you! Whether you have a question about our products, need to place a special order, or just want to say hello, please don\'t hesitate to reach out.</p>
<h3>General Enquiries</h3>
<p>Email: info@theskybakery.com.au<br>
Phone: 1800 SKY BAKE</p>
<h3>Store Locations</h3>
<p>Visit our <a href="/store-locations/">Store Locations</a> page for addresses and opening hours of all our stores.</p>',
        ),
    );

    foreach ($pages as $page_data) {
        $existing = get_page_by_title($page_data['title']);

        if ($existing) {
            continue;
        }

        $page_id = wp_insert_post(array(
            'post_title' => $page_data['title'],
            'post_content' => $page_data['content'],
            'post_type' => 'page',
            'post_status' => 'publish',
        ));

        if ($page_id && !is_wp_error($page_id) && !empty($page_data['template'])) {
            update_post_meta($page_id, '_wp_page_template', $page_data['template']);
        }
    }

    // Set front page
    $front_page = get_page_by_title('Home');
    if (!$front_page) {
        $front_page_id = wp_insert_post(array(
            'post_title' => 'Home',
            'post_type' => 'page',
            'post_status' => 'publish',
        ));
        update_option('show_on_front', 'page');
        update_option('page_on_front', $front_page_id);
    }

    return true;
}

/**
 * Import navigation menus
 */
function tsb_import_menus() {
    // Create primary menu
    $menu_name = 'Primary Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        // Add menu items
        $menu_items = array(
            array('title' => 'Home', 'url' => home_url('/'), 'order' => 1),
            array('title' => 'Menu', 'url' => home_url('/menu/'), 'order' => 2),
            array('title' => 'Cake Builder', 'url' => home_url('/cake-builder/'), 'order' => 3),
            array('title' => 'Stores', 'url' => home_url('/store-locations/'), 'order' => 4),
            array('title' => 'About', 'url' => home_url('/about-us/'), 'order' => 5),
            array('title' => 'Contact', 'url' => home_url('/contact/'), 'order' => 6),
        );
        
        foreach ($menu_items as $item) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => $item['title'],
                'menu-item-url' => $item['url'],
                'menu-item-status' => 'publish',
                'menu-item-position' => $item['order'],
            ));
        }
        
        // Assign menu to location
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    return true;
}

/**
 * Set theme options
 */
function tsb_set_theme_options() {
    // Set customizer options
    set_theme_mod('tsb_primary_color', '#c9a86c');
    set_theme_mod('tsb_secondary_color', '#2c2c2c');
    set_theme_mod('tsb_phone', '1800 SKY BAKE');
    set_theme_mod('tsb_email', 'info@theskybakery.com.au');
    set_theme_mod('tsb_address', 'Multiple Locations Across WA');
    set_theme_mod('tsb_facebook', 'https://facebook.com/theskybakery');
    set_theme_mod('tsb_instagram', 'https://instagram.com/theskybakery');
    set_theme_mod('tsb_footer_text', '© ' . date('Y') . ' TheSkyBakery. All rights reserved.');
    
    // Upload and set logo
    $logo_id = tsb_upload_image('/assets/images/logo.png', 0);
    if ($logo_id) {
        set_theme_mod('custom_logo', $logo_id);
    }
}

/**
 * Create custom cake product for cake builder
 */
function tsb_create_custom_cake_product() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    $existing = get_posts(array(
        'post_type' => 'product',
        'title' => 'Custom Cake',
        'post_status' => 'any',
        'numberposts' => 1,
    ));

    if (!empty($existing)) {
        return;
    }

    $product = new WC_Product_Simple();
    $product->set_name('Custom Cake');
    $product->set_description('Custom designed cake from our Cake Builder.');
    $product->set_regular_price('38.00');
    $product->set_status('private');
    $product->set_catalog_visibility('hidden');
    $product->set_virtual(true);
    $product->save();
}
add_action('after_switch_theme', 'tsb_create_custom_cake_product');
