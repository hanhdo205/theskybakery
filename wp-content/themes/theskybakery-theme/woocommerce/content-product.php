<?php
/**
 * Product Loop Content
 *
 * @package TheSkyBakery
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class('product-card', $product); ?>>
    <div class="product-card-inner" style="background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;">
        
        <div class="product-thumb" style="position: relative; overflow: hidden;">
            <?php if ($product->is_on_sale()) : ?>
                <span class="sale-badge" style="position: absolute; top: 15px; left: 15px; background: #e74c3c; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 2;">Sale!</span>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(get_permalink()); ?>" class="product-image-link">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('tsb-product-thumb', array(
                        'style' => 'width: 100%; height: 250px; object-fit: cover; transition: transform 0.5s ease;'
                    ));
                } else {
                    echo '<div style="width: 100%; height: 250px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">';
                    echo '<i class="fas fa-birthday-cake" style="font-size: 60px; color: #ddd;"></i>';
                    echo '</div>';
                }
                ?>
            </a>
            
            <div class="product-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 15px; background: linear-gradient(transparent, rgba(0,0,0,0.7)); opacity: 0; transition: opacity 0.3s ease;">
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>

        <div class="product-info" style="padding: 20px;">
            <?php
            $categories = get_the_terms($product->get_id(), 'product_cat');
            if ($categories && !is_wp_error($categories)) :
                $category = $categories[0];
            ?>
                <span class="product-category" style="color: var(--primary-color); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo esc_html($category->name); ?>
                </span>
            <?php endif; ?>
            
            <h3 class="product-title" style="font-family: var(--font-heading); font-size: 1.1rem; margin: 8px 0 10px;">
                <a href="<?php echo esc_url(get_permalink()); ?>" style="color: var(--secondary-color); text-decoration: none;">
                    <?php the_title(); ?>
                </a>
            </h3>
            
            <div class="product-price" style="font-weight: 600; color: var(--primary-color); font-size: 1.1rem;">
                <?php woocommerce_template_loop_price(); ?>
            </div>
        </div>
    </div>
</li>

<style>
.product-card-inner:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}
.product-card-inner:hover .product-thumb img {
    transform: scale(1.05);
}
.product-card-inner:hover .product-overlay {
    opacity: 1;
}
.product-overlay .button {
    display: block;
    width: 100%;
    background: var(--primary-color);
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
}
.product-overlay .button:hover {
    background: #b8974f;
}
</style>
