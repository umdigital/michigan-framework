<?php get_header(); ?>

<?php if( have_posts() ): ?>

<div id="page">
    <?php while (have_posts()) : the_post(); ?>
    <?php if( has_post_thumbnail() ): ?>
    <div class="featured-image">
        <?php the_post_thumbnail('featured-image'); ?>
    </div>
    <?php endif; ?>

    <div class="postWrapper" id="post-<?php the_ID(); ?>">
        <?php if( get_the_title() ): ?>
        <h2 class="postTitle"><?php the_title(); ?></h2>
        <?php endif; ?>
        <div class="post"><?php the_content(); ?></div>
    </div>

    <?php endwhile; ?>
</div>

<?php endif; ?>

<?php get_footer(); ?>
