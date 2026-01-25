<?php
/**
 * Front Page Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main front-page">
    
    <!-- Hero Slider Section -->
    <section class="hero-slider">
        <div class="slider-wrapper">
            <?php
            // Lấy slides từ post type tsb_slider
            $sliders = new WP_Query(array(
                'post_type'      => 'tsb_slider',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'post_status'    => 'publish',
            ));

            if ($sliders->have_posts()) :
                while ($sliders->have_posts()) : $sliders->the_post();
                    $slide_link = get_post_meta(get_the_ID(), '_slider_link', true);
                    $slide_text = get_post_meta(get_the_ID(), '_slider_text', true);
            ?>
                <div class="slide-item">
                    <?php if ($slide_link) : ?>
                        <a href="<?php echo esc_url($slide_link); ?>">
                    <?php endif; ?>

                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('full', array('alt' => get_the_title())); ?>
                    <?php endif; ?>

                    <?php if ($slide_text) : ?>
                        <div class="slide-content">
                            <h2><?php echo esc_html($slide_text); ?></h2>
                        </div>
                    <?php endif; ?>

                    <?php if ($slide_link) : ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                // Fallback: lấy từ Theme Options
                $slider_images = array();
                for ($i = 1; $i <= 5; $i++) {
                    $img = tsb_get_option('slider_image_' . $i);
                    if ($img) {
                        $slider_images[] = $img;
                    }
                }

                if (!empty($slider_images)) :
                    foreach ($slider_images as $image) :
            ?>
                <div class="slide-item">
                    <img src="<?php echo esc_url($image); ?>" alt="The Sky Bakery">
                </div>
            <?php
                    endforeach;
                else :
            ?>
                <!-- Default slides - thêm ảnh vào Sliders trong admin -->
                <div class="slide-item">
                    <img src="<?php echo TSB_THEME_URI; ?>/assets/images/sliders/slide-1.jpg" alt="The Sky Bakery">
                </div>
            <?php
                endif;
            endif;
            ?>
        </div>
    </section>

    <!-- Product Hot Section -->
    <section class="product-hot-section featured_product">
        <div class="container">
            <h2 class="headline-lg">Product <span>hot</span></h2>

            <?php if (class_exists('WooCommerce')) : ?>
                <div class="products-carousel owl-carousel owl-theme">
                    <?php
                    $args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => tsb_get_option('featured_products_count', 12),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );

                    // Check if featured products only
                    if (tsb_get_option('show_featured_only', false)) {
                        $args['tax_query'] = array(
                            array(
                                'taxonomy' => 'product_visibility',
                                'field'    => 'name',
                                'terms'    => 'featured',
                            ),
                        );
                    }

                    $products = new WP_Query($args);

                    if ($products->have_posts()) :
                        while ($products->have_posts()) : $products->the_post();
                            global $product;
                    ?>
                        <div class="product-item">
                            <div class="product-item-inner">
                                <div class="product-image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('tsb-product-thumb', array('alt' => get_the_title(), 'title' => get_the_title())); ?>
                                        <?php else : ?>
                                            <img src="<?php echo wc_placeholder_img_src('tsb-product-thumb'); ?>" alt="<?php the_title_attribute(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                </div>
                                <p class="product-price">
                                    <span class="current-price"><?php echo $product->get_price_html(); ?></span>
                                </p>
                            </div>
                            <div class="product-link">
                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" rel="nofollow" data-product_id="<?php echo esc_attr($product->get_id()); ?>" data-product_sku="" data-quantity="1" class="btn btn-cart add_to_cart_button product_type_simple">Add to cart</a>
                            </div>
                        </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>

                <!-- View All Button -->
                <div class="text-center mt-5">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-outline-primary">
                        View All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Custom Cake Builder CTA -->
	<!--
    <section class="cake-builder-cta">
        <div class="container">
            <div class="cta-content text-center">
                <h2>Design Your Perfect Cake</h2>
                <p>Create a custom cake for your special occasion with our easy-to-use cake builder</p>
                <a href="<?php //echo esc_url(home_url('/cake-builder')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-birthday-cake"></i> Start Building
                </a>
            </div>
        </div>
    </section>
	!-->

    <!-- Store Locations Preview -->
    <section class="stores-preview">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Our Locations</h2>
                <p>Visit us at any of our convenient locations</p>
            </div>

            <div class="stores-grid">
                <?php
                $stores = tsb_get_stores();
                $count = 0;
                if ($stores) :
                    foreach ($stores as $store) :
                        if ($count >= 3) break;
                        $address = get_post_meta($store->ID, '_store_address', true);
                        $phone = get_post_meta($store->ID, '_store_phone', true);
                ?>
                    <div class="store-card">
                        <h4><?php echo esc_html($store->post_title); ?></h4>
                        <?php if ($address) : ?>
                            <p class="address"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($address); ?></p>
                        <?php endif; ?>
                        <?php if ($phone) : ?>
                            <p class="phone"><i class="fas fa-phone"></i> <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
                        <?php endif; ?>
                    </div>
                <?php
                        $count++;
                    endforeach;
                endif;
                ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo esc_url(home_url('/store')); ?>" class="btn btn-outline-primary">
                    View All Stores
                </a>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
