<?php
/**
 * Template Name: Menu Page
 * 
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main menu-page">
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title"><?php the_title(); ?></h1>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <!-- Sidebar Categories -->
            <div class="col-lg-3">
                <aside class="menu-sidebar">
                    <div class="categories-list">
                        <ul class="category-menu">
                            <?php
                            $product_categories = get_terms(array(
                                'taxonomy'   => 'product_cat',
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                                'hide_empty' => true,
                                'parent'     => 0,
                            ));

                            if (!is_wp_error($product_categories) && !empty($product_categories)) :
                                foreach ($product_categories as $category) :
                            ?>
                                <li class="category-item">
                                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                    <?php
                                    // Get subcategories
                                    $subcategories = get_terms(array(
                                        'taxonomy'   => 'product_cat',
                                        'orderby'    => 'name',
                                        'order'      => 'ASC',
                                        'hide_empty' => true,
                                        'parent'     => $category->term_id,
                                    ));

                                    if (!is_wp_error($subcategories) && !empty($subcategories)) :
                                    ?>
                                        <ul class="subcategory-menu">
                                            <?php foreach ($subcategories as $subcategory) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url(get_term_link($subcategory)); ?>">
                                                        <?php echo esc_html($subcategory->name); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
                </aside>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="menu-products">
                    <?php if (class_exists('WooCommerce')) : ?>
                        <div class="products-grid menu-grid">
                            <?php
                            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                            $args = array(
                                'post_type'      => 'product',
                                'posts_per_page' => 24,
                                'paged'          => $paged,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                            );

                            $products = new WP_Query($args);

                            if ($products->have_posts()) :
                                while ($products->have_posts()) : $products->the_post();
                                    global $product;
                            ?>
                                <div class="product-card menu-product">
                                    <div class="product-image">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('tsb-product-thumb'); ?>
                                            <?php else : ?>
                                                <img src="<?php echo wc_placeholder_img_src('tsb-product-thumb'); ?>" alt="<?php the_title_attribute(); ?>">
                                            <?php endif; ?>
                                        </a>
                                        
                                        <?php if ($product->is_on_sale()) : ?>
                                            <span class="sale-badge">Sale!</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-info">
                                        <h3 class="product-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>
                                        <div class="product-price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                        <?php if ($product->get_short_description()) : ?>
                                            <div class="product-description">
                                                <?php echo wp_trim_words($product->get_short_description(), 15); ?>
                                            </div>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn btn-add-cart" data-product_id="<?php echo esc_attr($product->get_id()); ?>">
                                            Add to cart
                                        </a>
                                    </div>
                                </div>
                            <?php
                                endwhile;
                            endif;
                            ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($products->max_num_pages > 1) : ?>
                            <nav class="products-pagination">
                                <?php
                                echo paginate_links(array(
                                    'total'     => $products->max_num_pages,
                                    'current'   => $paged,
                                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                                ));
                                ?>
                            </nav>
                        <?php endif; ?>

                        <?php wp_reset_postdata(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
