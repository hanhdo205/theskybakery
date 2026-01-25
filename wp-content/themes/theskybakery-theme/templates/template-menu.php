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
	<!--
    <section class="page-header">
        <div class="container">
            <h1 class="page-title"><?php //the_title(); ?></h1>
        </div>
    </section>
	!-->

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
                <?php if (class_exists('WooCommerce')) : ?>

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
                    ?>

                    <div class="shop-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                        <div class="result-count" style="color: #666;">
                            <?php
                            $total = $products->found_posts;
                            $per_page = $products->query_vars['posts_per_page'];
                            $current = max(1, $paged);
                            $from = (($current - 1) * $per_page) + 1;
                            $to = min($total, $current * $per_page);
                            printf('Showing %d–%d of %d results', $from, $to, $total);
                            ?>
                        </div>
                    </div>

                    <ul class="products columns-3">
                        <?php
                        while ($products->have_posts()) : $products->the_post();
                            wc_get_template_part('content', 'product');
                        endwhile;
                        ?>
                    </ul>

                    <!-- Pagination -->
                    <?php if ($products->max_num_pages > 1) : ?>
                        <div class="pagination-wrap" style="margin-top: 40px;">
                            <?php
                            echo paginate_links(array(
                                'total'     => $products->max_num_pages,
                                'current'   => $paged,
                                'prev_text' => '&larr;',
                                'next_text' => '&rarr;',
                            ));
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php else : ?>

                        <div class="no-products" style="text-align: center; padding: 60px 20px;">
                            <i class="fas fa-birthday-cake" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                            <h3 style="font-family: var(--font-heading); margin-bottom: 10px;">No products found</h3>
                            <p style="color: #666;">Sorry, we couldn't find any products.</p>
                        </div>

                    <?php endif; ?>

                    <?php wp_reset_postdata(); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
