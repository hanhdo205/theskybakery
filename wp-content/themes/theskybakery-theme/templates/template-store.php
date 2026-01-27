<?php
/**
 * Template Name: Store Locations
 * 
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main store-page">
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title"><?php the_title(); ?></h1>
        </div>
    </section>

    <div class="container">
        <div class="stores-list">
            <?php
            $stores = tsb_get_stores();

            if ($stores) :
                foreach ($stores as $store) :
                    $address = get_post_meta($store->ID, '_store_address', true);
                    $phone = get_post_meta($store->ID, '_store_phone', true);
                    $email = get_post_meta($store->ID, '_store_email', true);
                    $hours = get_post_meta($store->ID, '_store_hours', true);
                    $map_embed = get_post_meta($store->ID, '_store_map_embed', true);
            ?>
                <div class="store-location-card">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="store-info">
                                <h3 class="store-name"><?php echo esc_html($store->post_title); ?></h3>
                                
                                <?php if ($address) : ?>
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="info-content">
                                            <strong>Address:</strong>
                                            <p><?php echo esc_html($address); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($phone) : ?>
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <div class="info-content">
                                            <strong>Phone:</strong>
                                            <p><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($email) : ?>
                                    <div class="info-item">
                                        <i class="fas fa-envelope"></i>
                                        <div class="info-content">
                                            <strong>Email:</strong>
                                            <p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($hours) : ?>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <div class="info-content">
                                            <strong>Opening Hours:</strong>
                                            <p><?php echo nl2br(esc_html($hours)); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="store-actions">
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($address); ?>" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-directions"></i> Get Directions
                                    </a>
                                    <?php if ($phone) : ?>
                                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-phone"></i> Call Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else :
            ?>
                <div class="no-stores">
                    <p><?php esc_html_e('No store locations found.', 'theskybakery'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();
