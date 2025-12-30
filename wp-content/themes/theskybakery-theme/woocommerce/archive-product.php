<?php
/**
 * Archive Product Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main shop-page">
    
    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #a8894d 100%); padding: 60px 0; text-align: center;">
        <div class="container">
            <h1 class="page-title" style="color: #fff; font-family: var(--font-heading); font-size: 3rem; margin-bottom: 10px;">
                <?php woocommerce_page_title(); ?>
            </h1>
            <?php if (is_product_category()) : ?>
                <?php
                $term = get_queried_object();
                if ($term && $term->description) :
                ?>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                        <?php echo esc_html($term->description); ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section" style="background: #f8f8f8; padding: 15px 0;">
        <div class="container">
            <?php woocommerce_breadcrumb(); ?>
        </div>
    </section>

    <div class="container" style="padding: 40px 0;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <aside class="shop-sidebar" style="position: sticky; top: 100px;">
                    <div class="sidebar-widget category-filter" style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); margin-bottom: 30px;">
                        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color);">Categories</h3>
                        <?php
                        $product_categories = get_terms(array(
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => 0,
                        ));
                        
                        if (!empty($product_categories) && !is_wp_error($product_categories)) :
                        ?>
                            <ul class="category-list" style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($product_categories as $category) : ?>
                                    <li style="margin-bottom: 10px;">
                                        <a href="<?php echo get_term_link($category); ?>" style="color: #666; text-decoration: none; display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; transition: all 0.3s;">
                                            <span><?php echo esc_html($category->name); ?></span>
                                            <span style="background: var(--light-bg); padding: 2px 10px; border-radius: 20px; font-size: 12px;"><?php echo esc_html($category->count); ?></span>
                                        </a>
                                        <?php
                                        $subcategories = get_terms(array(
                                            'taxonomy'   => 'product_cat',
                                            'hide_empty' => true,
                                            'parent'     => $category->term_id,
                                        ));
                                        
                                        if (!empty($subcategories) && !is_wp_error($subcategories)) :
                                        ?>
                                            <ul style="list-style: none; padding-left: 15px; margin: 5px 0;">
                                                <?php foreach ($subcategories as $subcategory) : ?>
                                                    <li style="margin-bottom: 5px;">
                                                        <a href="<?php echo get_term_link($subcategory); ?>" style="color: #888; text-decoration: none; font-size: 14px;">
                                                            <?php echo esc_html($subcategory->name); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Price Filter Widget -->
                    <?php if (is_active_sidebar('shop-sidebar')) : ?>
                        <?php dynamic_sidebar('shop-sidebar'); ?>
                    <?php endif; ?>
                </aside>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (woocommerce_product_loop()) : ?>

                    <div class="shop-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                        <div class="result-count">
                            <?php woocommerce_result_count(); ?>
                        </div>
                        <div class="catalog-ordering">
                            <?php woocommerce_catalog_ordering(); ?>
                        </div>
                    </div>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php
                    while (have_posts()) :
                        the_post();
                        wc_get_template_part('content', 'product');
                    endwhile;
                    ?>

                    <?php woocommerce_product_loop_end(); ?>

                    <div class="pagination-wrap" style="margin-top: 40px;">
                        <?php woocommerce_pagination(); ?>
                    </div>

                <?php else : ?>
                    
                    <div class="no-products" style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-birthday-cake" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                        <h3 style="font-family: var(--font-heading); margin-bottom: 10px;">No products found</h3>
                        <p style="color: #666;">Sorry, we couldn't find any products matching your criteria.</p>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary" style="margin-top: 20px;">View All Products</a>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<?php
get_footer();
