<?php header( 'X-UA-Compatible: IE=Edge' ); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?php wp_title( '|', true, 'right' ); ?> <?php echo get_bloginfo('name'); ?></title>

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php MichiganFramework::getGridOverlay(); ?>
<div id="wrapper">
    <a class="skip-link" href="#section-content">Skip to content</a>

    <?php get_template_part( 'templates/section-custom/before-header' ); ?>

    <div id="section-header" role="banner">
        <?php get_template_part( 'templates/section-header/prefix' ); ?>

        <div id="zone-header-branding">
            <div class="row">
                <?php get_template_part( 'templates/section-header/zone-header-branding/prefix' ); ?>

                <div class="logo-title <?php echo MichiganFramework::getColumns( 'header:branding' );?> columns">
                     <?php if( get_header_image() ): ?>
                    <h1 class="logo"><a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr( get_bloginfo('name', 'display') ); ?>" rel="home">
                        <img src="<?php header_image(); ?>" title="<?php echo esc_attr( get_bloginfo('name', 'display') ); ?>" alt="<?php echo esc_attr( get_bloginfo('name', 'display') ); ?>" />
                    </a></h1>
                    <?php else: ?>
                    <h1><a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr( get_bloginfo('name', 'display') ); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                    <?php endif; ?>
                </div>

                <?php get_template_part( 'templates/section-header/zone-header-branding/postfix' ); ?>
            </div>
        </div>

        <?php get_template_part( 'templates/section-header/postfix' ); ?>

        <?php if( has_nav_menu( 'header-menu', 'michigan_framework' ) ): ?>
        <div id="zone-header-menu" role="navigation" aria-label="Main Menu">
            <div class="row">
                <div class="<?php echo MichiganFramework::getColumns( 'menus:header_menu' );?> columns mfwMenu">
                    <a href="javascript:void(0);" class="hamburger-header"><h3><i class="fa fa-bars"></i>Menu</h3></a>
                     <?php wp_nav_menu(apply_filters( 'mfw-header-menu', array(
                        'container'      => '',
                        'fallback_cb'    => false,
                        'menu_class'     => 'header-menu clearfix',
                        'theme_location' => 'header-menu',
                        'depth'          => 1,
                        'link_before'    => '<span>',
                        'link_after'     => '</span>'
                    )));?> 
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- #SECTION-HEADER -->

    <?php get_template_part( 'templates/section-custom/before-content' ); ?>

    <div id="section-content" role="main">
        <?php get_template_part( 'templates/section-content/prefix' ); ?>

        <div id="zone-content">
            <div class="row">
                <?php get_template_part( 'templates/section-content/zone-content/prefix' ); ?>

                <?php MichiganFramework::displayWidget( 'content_first' ); ?>

                <div id="content-main" class="<?php echo MichiganFramework::getContentColumns( 'content:content_main' );?> columns">
                    <?php get_template_part( 'templates/section-content/zone-content/content-main-prefix' ); ?>

                    <div id="content">
