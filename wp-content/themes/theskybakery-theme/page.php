<?php
/**
 * Page Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main page-template">
    
    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #a8894d 100%); padding: 60px 0; text-align: center;">
        <div class="container">
            <h1 class="page-title" style="color: #fff; font-family: var(--font-heading); font-size: 3rem; margin-bottom: 0;">
                <?php the_title(); ?>
            </h1>
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

    <section class="page-content" style="padding: 60px 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php
                    while (have_posts()) :
                        the_post();
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('page-article'); ?> style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
                            
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="page-featured-image" style="margin-bottom: 30px; border-radius: 10px; overflow: hidden;">
                                    <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto;')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="page-content-inner" style="font-size: 1.1rem; line-height: 1.8; color: #444;">
                                <?php
                                the_content();

                                wp_link_pages(array(
                                    'before' => '<div class="page-links" style="margin-top: 30px;">' . __('Pages:', 'theskybakery'),
                                    'after'  => '</div>',
                                ));
                                ?>
                            </div>
							
							<div style="clear:both"></div>

                        </article>
                    <?php
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
