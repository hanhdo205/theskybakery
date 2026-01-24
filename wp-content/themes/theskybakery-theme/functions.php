<?php
/**
 * The Sky Bakery Theme Functions
 *
 * @package TheSkyBakery
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme Constants
define('TSB_THEME_VERSION', '1.0.0');
define('TSB_THEME_DIR', get_template_directory());
define('TSB_THEME_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function tsb_theme_setup() {
    // Load text domain
    load_theme_textdomain('theskybakery', TSB_THEME_DIR . '/languages');

    // Theme supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('automatic-feed-links');
    add_theme_support('customize-selective-refresh-widgets');

    // WooCommerce Support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Image sizes
    add_image_size('tsb-product-thumb', 600, 600, true);
    add_image_size('tsb-slider', 1920, 800, true);
    add_image_size('tsb-store-thumb', 400, 300, true);

    // Register navigation menus
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'theskybakery'),
        'top_menu'  => __('Top Menu', 'theskybakery'),
        'footer'    => __('Footer Menu', 'theskybakery'),
    ));
}
add_action('after_setup_theme', 'tsb_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function tsb_enqueue_scripts() {
    // Google Fonts - Bebas Neue (giống Block MyPage), Oswald fallback, Open Sans cho body
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap', array(), null);
    
    // Bootstrap
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');
    
    // Slick Slider
    wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css', array(), '1.8.1');
    wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css', array(), '1.8.1');

    // Owl Carousel (for products)
    wp_enqueue_style('owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array(), '2.3.4');
    wp_enqueue_style('owl-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array(), '2.3.4');

    // Animate.css
    wp_enqueue_style('animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1');
    
    // Theme Main CSS
    wp_enqueue_style('tsb-main', TSB_THEME_URI . '/assets/css/main.css', array('bootstrap'), TSB_THEME_VERSION);
    wp_enqueue_style('theskybakery-style', get_stylesheet_uri(), array('tsb-main'), TSB_THEME_VERSION);

    // Product Detail CSS (for all pages when WooCommerce is active)
    if (class_exists('WooCommerce')) {
        wp_enqueue_style('tsb-product-detail', TSB_THEME_URI . '/assets/css/product-detail.css', array('tsb-main'), TSB_THEME_VERSION);
    }

    // Product Stars JS (only on single product pages)
    if (is_product()) {
        wp_enqueue_script('tsb-product-stars', TSB_THEME_URI . '/assets/js/product-stars.js', array('jquery'), TSB_THEME_VERSION, true);
    }

    // jQuery
    wp_enqueue_script('jquery');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true);
    
    // Slick Slider
    wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);

    // Owl Carousel
    wp_enqueue_script('owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), '2.3.4', true);

    // Theme Main JS
    wp_enqueue_script('tsb-main', TSB_THEME_URI . '/assets/js/main.js', array('jquery', 'slick', 'owl-carousel'), TSB_THEME_VERSION, true);

    // Localize script
    wp_localize_script('tsb-main', 'tsb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tsb_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'tsb_enqueue_scripts');

/**
 * Register Widgets
 */
function tsb_widgets_init() {
    register_sidebar(array(
        'name'          => __('Footer Widget 1', 'theskybakery'),
        'id'            => 'footer-1',
        'description'   => __('Footer widget area 1', 'theskybakery'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Shop Sidebar', 'theskybakery'),
        'id'            => 'shop-sidebar',
        'description'   => __('Shop sidebar widget area', 'theskybakery'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'tsb_widgets_init');

/**
 * Include additional files
 */
 
require_once TSB_THEME_DIR . '/inc/custom-post-types.php';
require_once TSB_THEME_DIR . '/inc/customizer.php';
require_once TSB_THEME_DIR . '/inc/woocommerce-functions.php';
require_once TSB_THEME_DIR . '/inc/store-locations.php';
require_once TSB_THEME_DIR . '/inc/pickup-scheduling.php';
require_once TSB_THEME_DIR . '/inc/cake-builder.php';
require_once TSB_THEME_DIR . '/inc/ajax-handlers.php';
require_once TSB_THEME_DIR . '/inc/demo-import.php';

/**
 * Custom Excerpt Length
 */
function tsb_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'tsb_excerpt_length');

/**
 * Add body classes
 */
function tsb_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'front-page';
    }
    if (class_exists('WooCommerce')) {
        if (is_shop() || is_product_category() || is_product_tag() || is_product()) {
            $classes[] = 'woocommerce-active';
        }
    }
    return $classes;
}
add_filter('body_class', 'tsb_body_classes');

/**
 * Get theme option helper
 */
function tsb_get_option($key, $default = '') {
    return get_theme_mod($key, $default);
}



