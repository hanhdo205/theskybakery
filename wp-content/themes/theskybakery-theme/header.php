<?php
/**
 * Theme Header
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=666641330062436&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<?php wp_body_open(); ?>

<div id="page" class="site">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="top-bar-left">
                        <?php if (tsb_get_option('top_bar_text')) : ?>
                            <span class="promo-text"><?php echo esc_html(tsb_get_option('top_bar_text')); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="top-bar-right">
					    <div class="top-bar-right">
                        <nav>
							<ul class="top-menu">
							   
								<li><a href="<?php echo esc_url(home_url('/my-account')); ?>"><i class="fa fa-user"></i> My Account</a></li>
							
								<li><a href="<?php echo esc_url(home_url('/cart')); ?>"> <i class="fa fa-shopping-cart"></i>Cart</a></li>
							</ul>
						</nav>
						</div>
                        <?php
						/*
                        wp_nav_menu(array(
                            'theme_location' => 'top_menu',
                            'menu_class'     => 'top-menu',
                            'container'      => 'nav',
                            'fallback_cb'    => 'tsb_top_menu_fallback',
                        ));
						*/
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header id="masthead" class="site-header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <!-- Logo -->
                <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <img src="<?php echo TSB_THEME_URI; ?>/assets/images/logo.png" alt="<?php bloginfo('name'); ?>" class="logo">
                    <?php endif; ?>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primaryMenu" aria-controls="primaryMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation -->
                <div class="collapse navbar-collapse" id="primaryMenu">
                    <?php
                    wp_nav_menu(array(
                        'theme_location'  => 'primary',
                        'menu_class'      => 'navbar-nav ms-auto',
                        'container'       => false,
                        'fallback_cb'     => 'tsb_primary_menu_fallback',
                        'walker'          => new TSB_Nav_Walker(),
                    ));
                    ?>
                    
                    <!-- Cart Icon -->
                    <?php if (class_exists('WooCommerce')) : ?>
                        <div class="header-cart">
                            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-icon">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div id="content" class="site-content">

<?php
/**
 * Fallback menu functions
 */
function tsb_top_menu_fallback() {
    echo '<nav><ul class="top-menu">';
    echo '<li><a href="' . esc_url(wc_get_page_permalink('myaccount')) . '">My Account</a></li>';
    echo '<li><a href="' . esc_url(home_url('/contact-us')) . '">Contact</a></li>';
    echo '<li><a href="' . esc_url(wc_get_cart_url()) . '">Cart</a></li>';
    echo '</ul></nav>';
}

function tsb_primary_menu_fallback() {
    echo '<ul class="navbar-nav ms-auto">';
    echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/menu')) . '">Menu</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(wc_get_checkout_url()) . '">Order Online</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/store')) . '">Store</a></li>';
    echo '</ul>';
}

/**
 * Custom Nav Walker
 */
class TSB_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="dropdown-menu">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'nav-item';
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'dropdown';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';

        $atts = array();
        $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
        $atts['href']   = !empty($item->url) ? $item->url : '';
        $atts['class']  = 'nav-link';

        if (in_array('menu-item-has-children', $item->classes)) {
            $atts['class'] .= ' dropdown-toggle';
            $atts['data-bs-toggle'] = 'dropdown';
            $atts['aria-expanded'] = 'false';
        }

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $title . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}
