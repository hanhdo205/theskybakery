<?php
/**
 * Template Name: Cake Builder
 * Description: Custom cake builder page template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main cake-builder-page">
    
    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #a8894d 100%); padding: 60px 0; text-align: center;">
        <div class="container">
            <h1 class="page-title" style="color: #fff; font-family: var(--font-heading); font-size: 3rem; margin-bottom: 10px;">
                <?php the_title(); ?>
            </h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; max-width: 600px; margin: 0 auto;">
                Design your perfect cake with our easy-to-use builder
            </p>
        </div>
    </section>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section" style="background: #f8f8f8; padding: 15px 0;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="margin: 0; background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="<?php echo home_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Cake Builder Section -->
    <section class="cake-builder-section" style="padding: 60px 0;">
        <div class="container">
            
            <!-- Instructions -->
            <div class="builder-instructions" style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-family: var(--font-heading); color: var(--secondary-color); margin-bottom: 15px;">
                    Create Your Dream Cake
                </h2>
                <p style="color: #666; max-width: 700px; margin: 0 auto;">
                    Follow the simple steps below to customize your perfect cake. Choose your size, flavor, frosting, 
                    and toppings. Add a personal message and select your pickup details. It's that easy!
                </p>
            </div>

            <!-- Builder Steps Overview -->
            <div class="builder-steps-overview" style="display: flex; justify-content: center; gap: 20px; margin-bottom: 50px; flex-wrap: wrap;">
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">1</div>
                    <span style="font-size: 14px; color: #666;">Size</span>
                </div>
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">2</div>
                    <span style="font-size: 14px; color: #666;">Flavor</span>
                </div>
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">3</div>
                    <span style="font-size: 14px; color: #666;">Frosting</span>
                </div>
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">4</div>
                    <span style="font-size: 14px; color: #666;">Toppings</span>
                </div>
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">5</div>
                    <span style="font-size: 14px; color: #666;">Message</span>
                </div>
                <div class="step-indicator" style="text-align: center; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600;">6</div>
                    <span style="font-size: 14px; color: #666;">Pickup</span>
                </div>
            </div>

            <!-- The Cake Builder Shortcode -->
            <?php echo do_shortcode('[cake_builder]'); ?>

            <!-- Additional Info -->
            <div class="builder-info" style="margin-top: 60px; background: #f9f9f9; padding: 40px; border-radius: 10px;">
                <div class="row">
                    <div class="col-md-4" style="margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <i class="fas fa-clock" style="font-size: 40px; color: var(--primary-color); margin-bottom: 15px;"></i>
                            <h4 style="font-family: var(--font-heading); margin-bottom: 10px;">Advance Notice</h4>
                            <p style="color: #666; font-size: 14px;">Custom cakes require at least 24 hours advance notice for preparation.</p>
                        </div>
                    </div>
                    <div class="col-md-4" style="margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <i class="fas fa-store" style="font-size: 40px; color: var(--primary-color); margin-bottom: 15px;"></i>
                            <h4 style="font-family: var(--font-heading); margin-bottom: 10px;">Store Pickup</h4>
                            <p style="color: #666; font-size: 14px;">Pick up your custom cake from any of our 5 convenient locations.</p>
                        </div>
                    </div>
                    <div class="col-md-4" style="margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <i class="fas fa-phone" style="font-size: 40px; color: var(--primary-color); margin-bottom: 15px;"></i>
                            <h4 style="font-family: var(--font-heading); margin-bottom: 10px;">Need Help?</h4>
                            <p style="color: #666; font-size: 14px;">Contact us for special requests or large orders.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Popular Combinations -->
    <section class="popular-combinations" style="padding: 60px 0; background: #fff;">
        <div class="container">
            <h2 style="text-align: center; font-family: var(--font-heading); color: var(--secondary-color); margin-bottom: 40px;">
                Popular Combinations
            </h2>
            <div class="row">
                <div class="col-md-4" style="margin-bottom: 30px;">
                    <div class="combination-card" style="background: #f9f9f9; padding: 30px; border-radius: 10px; text-align: center;">
                        <h4 style="font-family: var(--font-heading); color: var(--primary-color); margin-bottom: 15px;">Classic Chocolate</h4>
                        <ul style="list-style: none; padding: 0; margin: 0 0 15px; color: #666;">
                            <li>Medium Size</li>
                            <li>Chocolate Flavor</li>
                            <li>Chocolate Ganache</li>
                            <li>Chocolate Drip Topping</li>
                        </ul>
                        <span style="font-weight: 600; color: var(--secondary-color);">From $65</span>
                    </div>
                </div>
                <div class="col-md-4" style="margin-bottom: 30px;">
                    <div class="combination-card" style="background: #f9f9f9; padding: 30px; border-radius: 10px; text-align: center;">
                        <h4 style="font-family: var(--font-heading); color: var(--primary-color); margin-bottom: 15px;">Berry Delight</h4>
                        <ul style="list-style: none; padding: 0; margin: 0 0 15px; color: #666;">
                            <li>Large Size</li>
                            <li>Vanilla Flavor</li>
                            <li>Cream Cheese Frosting</li>
                            <li>Fresh Fruits Topping</li>
                        </ul>
                        <span style="font-weight: 600; color: var(--secondary-color);">From $88</span>
                    </div>
                </div>
                <div class="col-md-4" style="margin-bottom: 30px;">
                    <div class="combination-card" style="background: #f9f9f9; padding: 30px; border-radius: 10px; text-align: center;">
                        <h4 style="font-family: var(--font-heading); color: var(--primary-color); margin-bottom: 15px;">Red Velvet Dream</h4>
                        <ul style="list-style: none; padding: 0; margin: 0 0 15px; color: #666;">
                            <li>Medium Size</li>
                            <li>Red Velvet Flavor</li>
                            <li>Cream Cheese Frosting</li>
                            <li>Edible Flowers</li>
                        </ul>
                        <span style="font-weight: 600; color: var(--secondary-color);">From $78</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
