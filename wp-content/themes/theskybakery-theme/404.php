<?php
/**
 * 404 Page Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main error-404">
    
    <section class="error-section" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 60px 20px;">
        <div class="container">
            <div class="error-content" style="max-width: 600px; margin: 0 auto;">
                <div class="error-icon" style="font-size: 120px; color: var(--primary-color); margin-bottom: 30px;">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <h1 style="font-family: var(--font-heading); font-size: 4rem; color: var(--secondary-color); margin-bottom: 15px;">404</h1>
                <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #666; margin-bottom: 20px;">Oops! This cake doesn't exist</h2>
                <p style="color: #888; margin-bottom: 30px;">The page you're looking for seems to have vanished like a fresh batch of cupcakes. Let's get you back on track!</p>
                
                <div class="error-actions" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo home_url(); ?>" class="btn btn-primary" style="background: var(--primary-color); color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="<?php echo home_url('/menu/'); ?>" class="btn btn-secondary" style="background: var(--secondary-color); color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-birthday-cake"></i> View Our Menu
                    </a>
                </div>

                <div class="search-form-wrap" style="margin-top: 40px;">
                    <p style="color: #666; margin-bottom: 15px;">Or search for what you're looking for:</p>
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
