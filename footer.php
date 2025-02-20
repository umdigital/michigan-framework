
                    </div> <!-- #CONTENT -->

                    <?php get_template_part( 'templates/section-content/zone-content/content-main-postfix' ); ?>
                </div>

                <?php get_template_part( 'templates/section-content/zone-content/postfix' ); ?>

                <?php MichiganFramework::displayWidget( 'content_second' ); ?>
            </div>
        </div>
    </div><!-- SECTION-CONTENT -->

    <?php get_template_part( 'templates/section-custom/before-footer' ); ?>

    <div id="section-footer" role="contentinfo">
        <?php get_template_part( 'templates/section-footer/prefix' ); ?>

        <div id="zone-footer">
            <div class="row">
                <?php get_template_part( 'templates/section-footer/zone-footer/prefix' ); ?>

                <div id="footer-content" class="<?php echo MichiganFramework::getColumns( 'footer:content' );?> columns">
                    <?php get_template_part( 'templates/footer' ); ?>
                </div>

                <?php get_template_part( 'templates/section-footer/zone-footer/postfix' ); ?>
            </div>
        </div>

        <?php get_template_part( 'templates/section-footer/postfix' ); ?>
    </div><!-- SECTION-FOOTER -->

    <?php get_template_part( 'templates/section-custom/after-footer' ); ?>
</div><!-- #WRAPPER -->

<?php wp_footer(); ?>

</body>
</html>
