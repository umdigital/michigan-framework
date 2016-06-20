<?php get_header(); ?>

<?php if( have_posts() ): ?>

<ol id="posts">
    <?php while (have_posts()) : the_post(); ?>

    <li class="postWrapper" id="post-<?php the_ID(); ?>">
        <h2 class="postTitle"><?php if( !is_single() ): ?><a href="<?php the_permalink() ?>" rel="bookmark"><?php endif; ?><?php the_title(); ?><?php if( !is_single() ): ?></a><?php endif; ?></h2>
        <small><?php the_date(); ?></small>

        <div class="post"><?php the_content(__('(more...)')); ?></div>

        <hr class="noCss" />
    </li>

    <?php if( have_comments() || comments_open() ):  comments_template(); endif; // Get wp-comments.php template ?>

    <?php endwhile; ?>
</ol>

<?php else: ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>

<?php if( MichiganFramework::willPaginate() ): ?>
<?php
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'type'    => 'list',
        'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format'  => '/page/%#%',
        'current' => max( 1, get_query_var('paged') ),
        'total'   => $wp_query->max_num_pages
    ));
?>
<?php endif; ?>

<?php get_footer(); ?>
