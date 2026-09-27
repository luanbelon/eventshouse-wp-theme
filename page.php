<?php
/**
 * The template for displaying all single pages
 *
 * @package EventsHouse
 */

get_header(); ?>

<main id="main-content" class="site-main">
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Beautiful Page Header -->
        <div class="page-header" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-bg)); padding: var(--space-4xl) 0; text-align: center;">
            <div class="container">
                <h1 style="color: var(--color-primary-dark); font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 0; font-family: var(--font-heading); font-weight: 700;">
                    <?php the_title(); ?>
                </h1>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container" style="padding: var(--space-4xl) 0; max-width: 1200px; margin: 0 auto;">
            <div class="entry-content" style="background: var(--color-white); padding: var(--space-3xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <?php the_content(); ?>
            </div>
        </div>
        
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
