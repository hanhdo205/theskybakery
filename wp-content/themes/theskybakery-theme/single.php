<?php
/**
 * Single Post Template
 *
 * @package TheSkyBakery
 */

get_header();
?>

<main id="primary" class="site-main single-post">
    
    <?php
    while (have_posts()) :
        the_post();
    ?>
        <!-- Page Header -->
        <section class="post-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #a8894d 100%); padding: 80px 0; text-align: center;">
            <div class="container">
                <div class="post-meta" style="color: rgba(255,255,255,0.8); margin-bottom: 15px;">
                    <span><?php echo get_the_date(); ?></span>
                    <span style="margin: 0 10px;">•</span>
                    <span><?php the_category(', '); ?></span>
                </div>
                <h1 class="post-title" style="color: #fff; font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 0; max-width: 800px; margin-left: auto; margin-right: auto;">
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
                        <li class="breadcrumb-item"><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                    </ol>
                </nav>
            </div>
        </section>

        <section class="post-content" style="padding: 60px 0;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-article'); ?> style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
                            
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-featured-image" style="margin: -40px -40px 30px; overflow: hidden;">
                                    <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto;')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="post-content-inner" style="font-size: 1.1rem; line-height: 1.8; color: #444;">
                                <?php
                                the_content();

                                wp_link_pages(array(
                                    'before' => '<div class="page-links" style="margin-top: 30px;">' . __('Pages:', 'theskybakery'),
                                    'after'  => '</div>',
                                ));
                                ?>
                            </div>

                            <!-- Tags -->
                            <?php if (has_tag()) : ?>
                                <div class="post-tags" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                                    <i class="fas fa-tags" style="color: var(--primary-color); margin-right: 10px;"></i>
                                    <?php the_tags('', ', ', ''); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Author Box -->
                            <div class="author-box" style="margin-top: 30px; padding: 25px; background: #f9f9f9; border-radius: 10px; display: flex; gap: 20px; align-items: center;">
                                <?php echo get_avatar(get_the_author_meta('ID'), 80, '', '', array('style' => 'border-radius: 50%;')); ?>
                                <div>
                                    <h4 style="margin: 0 0 5px; font-family: var(--font-heading);">
                                        <?php the_author(); ?>
                                    </h4>
                                    <p style="margin: 0; color: #666; font-size: 14px;">
                                        <?php echo get_the_author_meta('description') ? get_the_author_meta('description') : 'Author at TheSkyBakery'; ?>
                                    </p>
                                </div>
                            </div>

                        </article>

                        <!-- Post Navigation -->
                        <nav class="post-navigation" style="margin-top: 40px; display: flex; justify-content: space-between; gap: 20px;">
                            <?php
                            $prev_post = get_previous_post();
                            $next_post = get_next_post();
                            ?>
                            
                            <?php if ($prev_post) : ?>
                                <a href="<?php echo get_permalink($prev_post); ?>" style="flex: 1; background: #fff; padding: 20px; border-radius: 10px; text-decoration: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05); transition: all 0.3s;">
                                    <span style="color: #999; font-size: 12px; text-transform: uppercase;">← Previous</span>
                                    <h5 style="margin: 5px 0 0; color: var(--secondary-color); font-family: var(--font-heading);"><?php echo get_the_title($prev_post); ?></h5>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($next_post) : ?>
                                <a href="<?php echo get_permalink($next_post); ?>" style="flex: 1; background: #fff; padding: 20px; border-radius: 10px; text-decoration: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05); text-align: right; transition: all 0.3s;">
                                    <span style="color: #999; font-size: 12px; text-transform: uppercase;">Next →</span>
                                    <h5 style="margin: 5px 0 0; color: var(--secondary-color); font-family: var(--font-heading);"><?php echo get_the_title($next_post); ?></h5>
                                </a>
                            <?php endif; ?>
                        </nav>

                        <!-- Comments -->
                        <?php
                        if (comments_open() || get_comments_number()) :
                            comments_template();
                        endif;
                        ?>

                    </div>
                </div>
            </div>
        </section>

    <?php
    endwhile;
    ?>

</main>

<?php
get_footer();
