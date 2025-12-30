<?php
/**
 * Theme Footer
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

    </div><!-- #content -->

    <!-- Footer Main -->
    <footer id="colophon" class="site-footer">
        <!-- Newsletter & Social Row -->
        <div class="footer-main">
            <div class="container">
                <div class="row">
                    <!-- Register Column -->
                    <div class="col-lg-6 footer-register-col">
                        <div class="footer-register">
                            <h3>Register</h3>
                            <p>Receive promotional info</p>
                            <form action="#" method="post" class="register-form" id="newsletter-form">
                                <div class="input-group">
                                    <input type="email" name="email" placeholder="Enter your email" required>
                                    <button type="submit">Register <i class="fas fa-plus"></i></button>
                                </div>
                            </form>
                            <p class="tagline">do not miss any concessions</p>
                        </div>
                    </div>

                    <!-- Social Column -->
                    <div class="col-lg-6 footer-social-col">
                        <div class="footer-social">
                            <h3>Follow The Sky Bakery</h3>
                            <p>On Social Networks</p>
                            <div class="fb-buttons">
                                <a href="<?php echo esc_url(tsb_get_option('social_facebook', 'https://facebook.com')); ?>" target="_blank" rel="noopener" class="fb-like-btn">
                                    <i class="fab fa-facebook-f"></i> Like
                                </a>
                                <a href="<?php echo esc_url(tsb_get_option('social_facebook', 'https://facebook.com')); ?>" target="_blank" rel="noopener" class="fb-share-btn">
                                    Share
                                </a>
                            </div>
                            <p class="tagline">updated info everywhere</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="footer-brand">
                            <h4>The Sky Bakery</h4>
                            <p class="copyright">
                                <?php
                                echo esc_html(tsb_get_option('copyright_text',
                                    sprintf('Copyright 2017 - %s. The Sky Bakery And Patisserie. All Rights Reserved.',
                                        date('Y')
                                    )
                                ));
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="footer-social-icons">
                            <a href="<?php echo esc_url(tsb_get_option('social_facebook', 'https://facebook.com')); ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                            <a href="<?php echo esc_url(tsb_get_option('social_instagram', 'https://instagram.com')); ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                            <a href="mailto:<?php echo esc_attr(tsb_get_option('contact_email', 'info@theskybakery.com.au')); ?>"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Call for Order Box -->
    <div class="backtotop" id="callForOrderBox">
        Call for Order:
        <?php
        $stores = tsb_get_stores();
        if ($stores && !empty($stores)) :
            foreach ($stores as $store) :
                $phone = get_post_meta($store->ID, '_store_phone', true);
                if ($phone) :
        ?>
        <br>- <span class="store-name"><?php echo esc_html($store->post_title); ?></span> <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
        <?php
                endif;
            endforeach;
        else :
            $default_stores = array(
                array('name' => 'Lakelands Shopping Center', 'phone' => '08 9508 0751'),
                array('name' => 'Mandurah Forum', 'phone' => '08 9555 9397'),
                array('name' => 'Rockingham Center', 'phone' => '08 9508 0750'),
                array('name' => 'Kwinana', 'phone' => '08 6205 9984'),
                array('name' => 'Warnbro', 'phone' => '08 6185 4883'),
            );
            foreach ($default_stores as $store) :
        ?>
        <br>- <span class="store-name"><?php echo esc_html($store['name']); ?></span> <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $store['phone'])); ?>"><?php echo esc_html($store['phone']); ?></a>
        <?php
            endforeach;
        endif;
        ?>
    </div>

    <!-- Back to Top -->
    <div id="topcontrol" class="backtotop-btn">Top <i class="fa fa-arrow-circle-up"></i></div>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
