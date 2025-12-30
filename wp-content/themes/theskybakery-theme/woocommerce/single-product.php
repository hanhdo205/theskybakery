<?php
/**
 * Single Product Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main single-product-page">
    
    <!-- Breadcrumb -->
    <section class="breadcrumb-section" style="background: #f8f8f8; padding: 15px 0;">
        <div class="container">
            <?php woocommerce_breadcrumb(); ?>
        </div>
    </section>

    <div class="container" style="padding: 40px 0;">
        <?php
        while (have_posts()) :
            the_post();
            wc_get_template_part('content', 'single-product');
        endwhile;
        ?>
    </div>

</main>

<?php
get_footer();
