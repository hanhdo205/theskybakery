<?php
/**
 * Theme Customizer
 * 
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register customizer settings and controls
 */
function tsb_customize_register($wp_customize) {
    
    // ===========================================
    // THEME OPTIONS PANEL
    // ===========================================
    $wp_customize->add_panel('tsb_theme_options', array(
        'title'       => __('Theme Options', 'theskybakery'),
        'description' => __('Customize The Sky Bakery theme settings', 'theskybakery'),
        'priority'    => 30,
    ));

    // ===========================================
    // GENERAL SETTINGS SECTION
    // ===========================================
    $wp_customize->add_section('tsb_general_settings', array(
        'title'    => __('General Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 10,
    ));

    // Primary Color
    $wp_customize->add_setting('tsb_primary_color', array(
        'default'           => '#c9a86c',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tsb_primary_color', array(
        'label'    => __('Primary Color', 'theskybakery'),
        'section'  => 'tsb_general_settings',
        'settings' => 'tsb_primary_color',
    )));

    // Secondary Color
    $wp_customize->add_setting('tsb_secondary_color', array(
        'default'           => '#2c2c2c',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tsb_secondary_color', array(
        'label'    => __('Secondary Color', 'theskybakery'),
        'section'  => 'tsb_general_settings',
        'settings' => 'tsb_secondary_color',
    )));

    // Accent Color
    $wp_customize->add_setting('tsb_accent_color', array(
        'default'           => '#d4a574',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tsb_accent_color', array(
        'label'    => __('Accent Color', 'theskybakery'),
        'section'  => 'tsb_general_settings',
        'settings' => 'tsb_accent_color',
    )));

    // ===========================================
    // HEADER SECTION
    // ===========================================
    $wp_customize->add_section('tsb_header_settings', array(
        'title'    => __('Header Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 20,
    ));

    // Logo Height
    $wp_customize->add_setting('tsb_logo_height', array(
        'default'           => '60',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('tsb_logo_height', array(
        'label'       => __('Logo Height (px)', 'theskybakery'),
        'section'     => 'tsb_header_settings',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 30,
            'max'  => 150,
            'step' => 5,
        ),
    ));

    // Sticky Header
    $wp_customize->add_setting('tsb_sticky_header', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_sticky_header', array(
        'label'   => __('Enable Sticky Header', 'theskybakery'),
        'section' => 'tsb_header_settings',
        'type'    => 'checkbox',
    ));

    // Top Bar Enable
    $wp_customize->add_setting('tsb_topbar_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_topbar_enable', array(
        'label'   => __('Enable Top Bar', 'theskybakery'),
        'section' => 'tsb_header_settings',
        'type'    => 'checkbox',
    ));

    // Top Bar Text
    $wp_customize->add_setting('tsb_topbar_text', array(
        'default'           => 'Free pickup available at all locations!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_topbar_text', array(
        'label'   => __('Top Bar Text', 'theskybakery'),
        'section' => 'tsb_header_settings',
        'type'    => 'text',
    ));

    // ===========================================
    // CONTACT INFO SECTION
    // ===========================================
    $wp_customize->add_section('tsb_contact_settings', array(
        'title'    => __('Contact Information', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 30,
    ));

    // Main Phone
    $wp_customize->add_setting('tsb_main_phone', array(
        'default'           => '(08) 9534 8822',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_main_phone', array(
        'label'   => __('Main Phone Number', 'theskybakery'),
        'section' => 'tsb_contact_settings',
        'type'    => 'text',
    ));

    // Main Email
    $wp_customize->add_setting('tsb_main_email', array(
        'default'           => 'info@theskybakery.com.au',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('tsb_main_email', array(
        'label'   => __('Main Email Address', 'theskybakery'),
        'section' => 'tsb_contact_settings',
        'type'    => 'email',
    ));

    // Business Hours
    $wp_customize->add_setting('tsb_business_hours', array(
        'default'           => 'Mon-Fri: 7am-5pm | Sat: 8am-4pm | Sun: 9am-3pm',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_business_hours', array(
        'label'   => __('Business Hours', 'theskybakery'),
        'section' => 'tsb_contact_settings',
        'type'    => 'text',
    ));

    // ===========================================
    // SOCIAL MEDIA SECTION
    // ===========================================
    $wp_customize->add_section('tsb_social_settings', array(
        'title'    => __('Social Media', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 40,
    ));

    $social_networks = array(
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'twitter'   => 'Twitter/X',
        'youtube'   => 'YouTube',
        'pinterest' => 'Pinterest',
        'tiktok'    => 'TikTok',
    );

    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting('tsb_social_' . $network, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control('tsb_social_' . $network, array(
            'label'   => sprintf(__('%s URL', 'theskybakery'), $label),
            'section' => 'tsb_social_settings',
            'type'    => 'url',
        ));
    }

    // ===========================================
    // FOOTER SECTION
    // ===========================================
    $wp_customize->add_section('tsb_footer_settings', array(
        'title'    => __('Footer Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 50,
    ));

    // Footer Logo
    $wp_customize->add_setting('tsb_footer_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'tsb_footer_logo', array(
        'label'   => __('Footer Logo', 'theskybakery'),
        'section' => 'tsb_footer_settings',
    )));

    // Footer About Text
    $wp_customize->add_setting('tsb_footer_about', array(
        'default'           => 'The Sky Bakery has been serving the community with delicious cakes and pastries since 2010. We use only the finest ingredients to create memorable treats for every occasion.',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('tsb_footer_about', array(
        'label'   => __('About Text', 'theskybakery'),
        'section' => 'tsb_footer_settings',
        'type'    => 'textarea',
    ));

    // Copyright Text
    $wp_customize->add_setting('tsb_copyright_text', array(
        'default'           => '© %year% The Sky Bakery. All rights reserved.',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('tsb_copyright_text', array(
        'label'       => __('Copyright Text', 'theskybakery'),
        'description' => __('Use %year% for current year', 'theskybakery'),
        'section'     => 'tsb_footer_settings',
        'type'        => 'text',
    ));

    // Newsletter Enable
    $wp_customize->add_setting('tsb_newsletter_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_newsletter_enable', array(
        'label'   => __('Enable Newsletter Section', 'theskybakery'),
        'section' => 'tsb_footer_settings',
        'type'    => 'checkbox',
    ));

    // Newsletter Title
    $wp_customize->add_setting('tsb_newsletter_title', array(
        'default'           => 'Subscribe to Our Newsletter',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_newsletter_title', array(
        'label'   => __('Newsletter Title', 'theskybakery'),
        'section' => 'tsb_footer_settings',
        'type'    => 'text',
    ));

    // Newsletter Description
    $wp_customize->add_setting('tsb_newsletter_desc', array(
        'default'           => 'Get updates on new products, special offers, and seasonal treats!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_newsletter_desc', array(
        'label'   => __('Newsletter Description', 'theskybakery'),
        'section' => 'tsb_footer_settings',
        'type'    => 'text',
    ));

    // ===========================================
    // HOMEPAGE SECTION
    // ===========================================
    $wp_customize->add_section('tsb_homepage_settings', array(
        'title'    => __('Homepage Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 60,
    ));

    // Hero Slider Enable
    $wp_customize->add_setting('tsb_hero_slider_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_hero_slider_enable', array(
        'label'   => __('Enable Hero Slider', 'theskybakery'),
        'section' => 'tsb_homepage_settings',
        'type'    => 'checkbox',
    ));

    // Slider Autoplay Speed
    $wp_customize->add_setting('tsb_slider_speed', array(
        'default'           => '5000',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_slider_speed', array(
        'label'       => __('Slider Autoplay Speed (ms)', 'theskybakery'),
        'section'     => 'tsb_homepage_settings',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 2000,
            'max'  => 10000,
            'step' => 500,
        ),
    ));

    // Featured Products Count
    $wp_customize->add_setting('tsb_featured_count', array(
        'default'           => '8',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_featured_count', array(
        'label'       => __('Featured Products Count', 'theskybakery'),
        'section'     => 'tsb_homepage_settings',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 4,
            'max'  => 16,
            'step' => 4,
        ),
    ));

    // Featured Section Title
    $wp_customize->add_setting('tsb_featured_title', array(
        'default'           => 'Our Delicious Treats',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_featured_title', array(
        'label'   => __('Featured Section Title', 'theskybakery'),
        'section' => 'tsb_homepage_settings',
        'type'    => 'text',
    ));

    // CTA Section Enable
    $wp_customize->add_setting('tsb_cta_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_cta_enable', array(
        'label'   => __('Enable Cake Builder CTA', 'theskybakery'),
        'section' => 'tsb_homepage_settings',
        'type'    => 'checkbox',
    ));

    // CTA Background Image
    $wp_customize->add_setting('tsb_cta_bg_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'tsb_cta_bg_image', array(
        'label'   => __('CTA Background Image', 'theskybakery'),
        'section' => 'tsb_homepage_settings',
    )));

    // ===========================================
    // SHOP SETTINGS SECTION
    // ===========================================
    $wp_customize->add_section('tsb_shop_settings', array(
        'title'    => __('Shop Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 70,
    ));

    // Products Per Page
    $wp_customize->add_setting('tsb_products_per_page', array(
        'default'           => '24',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_products_per_page', array(
        'label'       => __('Products Per Page', 'theskybakery'),
        'section'     => 'tsb_shop_settings',
        'type'        => 'select',
        'choices'     => array(
            '12' => '12',
            '16' => '16',
            '20' => '20',
            '24' => '24',
            '28' => '28',
            '32' => '32',
        ),
    ));

    // Products Per Row
    $wp_customize->add_setting('tsb_products_columns', array(
        'default'           => '4',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_products_columns', array(
        'label'   => __('Products Per Row', 'theskybakery'),
        'section' => 'tsb_shop_settings',
        'type'    => 'select',
        'choices' => array(
            '3' => '3',
            '4' => '4',
            '5' => '5',
        ),
    ));

    // Sale Badge Text
    $wp_customize->add_setting('tsb_sale_badge_text', array(
        'default'           => 'Sale!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tsb_sale_badge_text', array(
        'label'   => __('Sale Badge Text', 'theskybakery'),
        'section' => 'tsb_shop_settings',
        'type'    => 'text',
    ));

    // Quick View Enable
    $wp_customize->add_setting('tsb_quickview_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tsb_sanitize_checkbox',
    ));
    $wp_customize->add_control('tsb_quickview_enable', array(
        'label'   => __('Enable Quick View', 'theskybakery'),
        'section' => 'tsb_shop_settings',
        'type'    => 'checkbox',
    ));

    // ===========================================
    // PICKUP SETTINGS SECTION
    // ===========================================
    $wp_customize->add_section('tsb_pickup_settings', array(
        'title'    => __('Pickup Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 80,
    ));

    // Minimum Notice Days
    $wp_customize->add_setting('tsb_min_notice_days', array(
        'default'           => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_min_notice_days', array(
        'label'       => __('Minimum Notice (Days)', 'theskybakery'),
        'description' => __('Minimum days in advance for pickup orders', 'theskybakery'),
        'section'     => 'tsb_pickup_settings',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 0,
            'max'  => 7,
            'step' => 1,
        ),
    ));

    // Custom Cake Notice Days
    $wp_customize->add_setting('tsb_custom_cake_notice', array(
        'default'           => '3',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_custom_cake_notice', array(
        'label'       => __('Custom Cake Notice (Days)', 'theskybakery'),
        'description' => __('Minimum days for custom cake orders', 'theskybakery'),
        'section'     => 'tsb_pickup_settings',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 14,
            'step' => 1,
        ),
    ));

    // Time Slot Interval
    $wp_customize->add_setting('tsb_time_slot_interval', array(
        'default'           => '30',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_time_slot_interval', array(
        'label'   => __('Time Slot Interval (minutes)', 'theskybakery'),
        'section' => 'tsb_pickup_settings',
        'type'    => 'select',
        'choices' => array(
            '15' => '15 minutes',
            '30' => '30 minutes',
            '60' => '1 hour',
        ),
    ));

    // ===========================================
    // CAKE BUILDER SECTION
    // ===========================================
    $wp_customize->add_section('tsb_cake_builder_settings', array(
        'title'    => __('Cake Builder Settings', 'theskybakery'),
        'panel'    => 'tsb_theme_options',
        'priority' => 90,
    ));

    // Cake Builder Page
    $wp_customize->add_setting('tsb_cake_builder_page', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_builder_page', array(
        'label'   => __('Cake Builder Page', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'dropdown-pages',
    ));

    // Base Price (Small)
    $wp_customize->add_setting('tsb_cake_price_small', array(
        'default'           => '38',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_price_small', array(
        'label'   => __('Small Cake Price ($)', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'number',
    ));

    // Base Price (Medium)
    $wp_customize->add_setting('tsb_cake_price_medium', array(
        'default'           => '55',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_price_medium', array(
        'label'   => __('Medium Cake Price ($)', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'number',
    ));

    // Base Price (Large)
    $wp_customize->add_setting('tsb_cake_price_large', array(
        'default'           => '75',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_price_large', array(
        'label'   => __('Large Cake Price ($)', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'number',
    ));

    // Base Price (XL)
    $wp_customize->add_setting('tsb_cake_price_xl', array(
        'default'           => '95',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_price_xl', array(
        'label'   => __('Extra Large Cake Price ($)', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'number',
    ));

    // Max Message Length
    $wp_customize->add_setting('tsb_cake_message_max', array(
        'default'           => '50',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('tsb_cake_message_max', array(
        'label'   => __('Max Message Length', 'theskybakery'),
        'section' => 'tsb_cake_builder_settings',
        'type'    => 'number',
    ));
}
add_action('customize_register', 'tsb_customize_register');

/**
 * Sanitize checkbox
 */
function tsb_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Output custom CSS from customizer settings
 */
function tsb_customizer_css() {
    $primary_color   = get_theme_mod('tsb_primary_color', '#c9a86c');
    $secondary_color = get_theme_mod('tsb_secondary_color', '#2c2c2c');
    $accent_color    = get_theme_mod('tsb_accent_color', '#d4a574');
    $logo_height     = get_theme_mod('tsb_logo_height', '60');
    ?>
    <style type="text/css" id="tsb-customizer-css">
        :root {
            --tsb-primary-color: <?php echo esc_attr($primary_color); ?>;
            --tsb-secondary-color: <?php echo esc_attr($secondary_color); ?>;
            --tsb-accent-color: <?php echo esc_attr($accent_color); ?>;
        }
        
        .site-header .logo img {
            max-height: <?php echo esc_attr($logo_height); ?>px;
        }
        
        /* Primary Color Applications */
        .btn-primary,
        .cart-icon .cart-count,
        .product-card .add-to-cart-btn,
        .newsletter-section,
        .cake-builder .step.active,
        .pickup-date-btn.selected {
            background-color: var(--tsb-primary-color);
        }
        
        a:hover,
        .primary-nav a:hover,
        .product-card .price,
        .store-card .phone a {
            color: var(--tsb-primary-color);
        }
        
        .btn-outline-primary {
            border-color: var(--tsb-primary-color);
            color: var(--tsb-primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--tsb-primary-color);
        }
        
        /* Secondary Color Applications */
        .top-bar,
        .site-footer,
        .product-card .overlay {
            background-color: var(--tsb-secondary-color);
        }
        
        /* Accent Color Applications */
        .category-badge,
        .sale-badge,
        .featured-tag {
            background-color: var(--tsb-accent-color);
        }
    </style>
    <?php
}
add_action('wp_head', 'tsb_customizer_css', 100);

/**
 * Customizer preview live JS
 */
function tsb_customize_preview_js() {
    wp_enqueue_script(
        'tsb-customizer-preview',
        get_template_directory_uri() . '/assets/js/customizer-preview.js',
        array('customize-preview'),
        TSB_VERSION,
        true
    );
}
add_action('customize_preview_init', 'tsb_customize_preview_js');

/**
 * Get copyright text with year replacement
 */
function tsb_get_copyright_text() {
    $text = get_theme_mod('tsb_copyright_text', '© %year% The Sky Bakery. All rights reserved.');
    return str_replace('%year%', date('Y'), $text);
}

/**
 * Get social media links
 */
function tsb_get_social_links() {
    $networks = array('facebook', 'instagram', 'twitter', 'youtube', 'pinterest', 'tiktok');
    $links = array();
    
    foreach ($networks as $network) {
        $url = get_theme_mod('tsb_social_' . $network, '');
        if (!empty($url)) {
            $links[$network] = $url;
        }
    }
    
    return $links;
}

/**
 * Output social media icons
 */
function tsb_social_icons($class = '') {
    $links = tsb_get_social_links();
    
    if (empty($links)) {
        return;
    }
    
    $icons = array(
        'facebook'  => 'fab fa-facebook-f',
        'instagram' => 'fab fa-instagram',
        'twitter'   => 'fab fa-twitter',
        'youtube'   => 'fab fa-youtube',
        'pinterest' => 'fab fa-pinterest-p',
        'tiktok'    => 'fab fa-tiktok',
    );
    
    echo '<div class="social-icons ' . esc_attr($class) . '">';
    foreach ($links as $network => $url) {
        printf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="social-icon social-%s" aria-label="%s"><i class="%s"></i></a>',
            esc_url($url),
            esc_attr($network),
            esc_attr(ucfirst($network)),
            esc_attr($icons[$network])
        );
    }
    echo '</div>';
}
