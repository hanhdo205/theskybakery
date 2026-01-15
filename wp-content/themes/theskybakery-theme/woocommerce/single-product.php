<?php
/**
 * Single Product Template
 * Beautiful Bakery Theme Design
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main single-product-page">

    <!-- Breadcrumb Section -->
    <section class="product-breadcrumb">
        <div class="container">
            <?php woocommerce_breadcrumb(); ?>
        </div>
    </section>

    <?php while (have_posts()) : the_post(); ?>

        <div class="container single-product-container">

            <!-- Product Main Section - 2 Column Layout -->
            <div class="row product-main-section">

                <!-- Left Column - Product Images -->
                <div class="col-lg-6 col-md-12 product-images-column">
                    <?php
                    /**
                     * woocommerce_before_single_product_summary hook.
                     *
                     * @hooked woocommerce_show_product_sale_flash - 10
                     * @hooked woocommerce_show_product_images - 20
                     */
                    do_action('woocommerce_before_single_product_summary');
                    ?>
                </div>

                <!-- Right Column - Product Summary -->
                <div class="col-lg-6 col-md-12 product-summary-column">
                    <div class="summary entry-summary">

                        <?php
                        /**
                         * woocommerce_single_product_summary hook.
                         *
                         * @hooked woocommerce_template_single_title - 5
                         * @hooked woocommerce_template_single_rating - 10
                         * @hooked woocommerce_template_single_price - 10
                         * @hooked woocommerce_template_single_excerpt - 20
                         * @hooked woocommerce_template_single_add_to_cart - 30
                         * @hooked woocommerce_template_single_meta - 40
                         * @hooked woocommerce_template_single_sharing - 50
                         */
                        do_action('woocommerce_single_product_summary');
                        ?>

                        <!-- Product Features -->
                        <div class="product-features">
                            <div class="feature-item">
                                <i class="fas fa-shipping-fast"></i>
                                <span><?php _e('Fast Delivery', 'theskybakery'); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-award"></i>
                                <span><?php _e('Premium Quality', 'theskybakery'); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-leaf"></i>
                                <span><?php _e('Fresh Ingredients', 'theskybakery'); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <span><?php _e('100% Guarantee', 'theskybakery'); ?></span>
                            </div>
                        </div>

                    </div><!-- .summary -->
                </div>

            </div><!-- .row -->

            <!-- Product Tabs & Related Products -->
            <div class="row">
                <div class="col-12">
                    <?php
                    /**
                     * woocommerce_after_single_product_summary hook.
                     *
                     * @hooked woocommerce_output_product_data_tabs - 10
                     * @hooked woocommerce_upsell_display - 15
                     * @hooked woocommerce_output_related_products - 20
                     */
                    do_action('woocommerce_after_single_product_summary');
                    ?>
                </div>
            </div>

        </div><!-- .container -->

    <?php endwhile; ?>

</main>

<?php
get_footer();
