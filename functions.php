<?php

/*-----------------------------------------------------------------------------------*/
/* Set Proper Parent/Child theme paths for inclusion
/*-----------------------------------------------------------------------------------*/

@define( 'PARENT_DIR', get_template_directory() );
@define( 'CHILD_DIR', get_stylesheet_directory() );

@define( 'PARENT_URL', get_template_directory_uri() );
@define( 'CHILD_URL', get_stylesheet_directory_uri() );

class MichiganFramework
{
    static private $_version            = 1.0;
    static private $_foundationVersion  = '6.2.3';
    static private $_fontAwesomeVersion = '4.7.0';

    static private $_config             = array();
    static private $_accordions         = 0;

    static private $_gitUpdate = array(
        'dir' => 'michigan-framework',
        'url' => 'https://github.com/umdigital/michigan-framework',
        'zip' => 'https://github.com/umdigital/michigan-framework/zipball/master',
        'raw' => 'https://raw.githubusercontent.com/umdigital/michigan-framework/master',
    );

    /**
     * Load config, add actions/filters
     **/
    static public function init()
    {
        // just in case it wasn't installed with the intended name
        self::$_gitUpdate['dir'] = basename( __DIR__ );

        // get current version
        $theme = wp_get_theme( self::$_gitUpdate['dir'] );
        self::$_version = $theme->get( 'Version' );

        /** LOAD CONFIG **/
        if( is_file( PARENT_DIR .'/config.json' ) ) {
            self::$_config = json_decode( file_get_contents( PARENT_DIR .'/config.json' ), true );
        }

        // check for child theme config and merge onto default
        $childConfig = false;
        if( (CHILD_DIR != PARENT_DIR) && is_file( CHILD_DIR .'/config.json' ) ) {
            $childConfig = json_decode( file_get_contents( CHILD_DIR .'/config.json' ), true );

            self::$_config = array_replace_recursive(
                self::$_config,
                $childConfig
            );
        }

        // default grid columns (defaults to 12)
        if( !isset( self::$_config['grid'] ) || !in_array( self::$_config['grid'], array( 16 ) ) ) {
            self::$_config['grid'] = 12;
        }
        // replace config with col# defaults
        else if( is_file( PARENT_DIR .'/config-'. self::$_config['grid'] .'col.json' ) ) {
            if( $tColConfig = json_decode( file_get_contents( PARENT_DIR .'/config-'. self::$_config['grid'] .'col.json' ), true ) ) {
                self::$_config = array_replace_recursive(
                    self::$_config,
                    $tColConfig
                );

                // remerge child config on new defaults
                if( $childConfig ) {
                    self::$_config = array_replace_recursive(
                        self::$_config,
                        $childConfig
                    );
                }
            }
        }

        // tweak debug config if needed
        if( self::$_config['debug']['enabled'] && !self::$_config['debug']['showall'] && !is_user_logged_in() ) {
            self::$_config['debug']['enabled'] = 0;
        }


        /** REGISTER WIDGET LOCATIONS **/
        foreach( self::$_config['widgets'] as $key => $config ) {
            if( !isset( $config['enabled'] ) || ($config['enabled'] !== false) ) {
                $config['name'] = isset( $config['name'] ) && $config['name']
                                ? $config['name']
                                : ucwords( str_replace( '_', ' ', $key ) );

                register_sidebar(apply_filters( 'widget--'. $key, array(
                    'name'          => __( $config['name'], 'michigan_framework' ),
                    'id'            => $key,
                    'before_widget' => '<div id="%1$s" class="widget %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h4 class="widget-title">',
                    'after_title'   => '</h4>'
                )));
            }
        }

        // REGISTER ACTIONS/FILTERS
        add_action( 'after_setup_theme', 'MichiganFramework::setupTheme' );
        add_action( 'wp_enqueue_scripts', 'MichiganFramework::enqueue', 1 );
        add_action( 'wp_head', 'MichiganFramework::wpHead', 99 );
        add_action( 'wp_footer', 'MichiganFramework::wpFooter', 99 );
        add_filter( 'posts_join', 'MichiganFramework::searchJoin' );
        add_filter( 'posts_where', 'MichiganFramework::searchWhere' );
        add_filter( 'posts_distinct', 'MichiganFramework::searchDistinct' );
        add_filter( 'upload_mimes', 'MichiganFramework::customUploadTypes' );
        add_filter( 'the_content', 'MichiganFramework::shortcodeEmptyParagraphFix' );

        if( !is_admin() ) {
            add_action( 'wp_before_admin_bar_render', 'MichiganFramework::adminBarRender' );
        }
        add_filter( 'image_size_names_choose', 'MichiganFramework::adminImageSizes' );

        add_filter( 'body_class', 'MichiganFramework::bodyClass' );
        add_filter( 'excerpt_more', 'MichiganFramework::excerptMore' );

        // ALLOW SHORTCODES IN TEXT WIDGET
        add_filter('widget_text', 'do_shortcode');

        // ADD SHORTCODES
        add_shortcode( 'accordion', 'MichiganFramework::shortcodeAccordion' );

        // ADD EDITOR BLOCKS
        add_action( 'init', function(){
            if( function_exists( 'register_block_type' ) ) {
                // accordion block
                wp_register_style(
                    'michigan-framework--accordion-ed-css',
                    PARENT_URL . '/blocks/accordion/editor.css',
                    array(),
                    filemtime( PARENT_DIR . '/blocks/accordion/editor.css' )
                );
                wp_register_script(
                    'michigan-framework--accordion-ed-js',
                    PARENT_URL . '/blocks/accordion/editor.js',
                    array( 'wp-blocks', 'wp-element', 'wp-editor' ),
                    filemtime( PARENT_DIR . '/blocks/accordion/editor.js' )
                );
                register_block_type( 'michigan-framework/accordion', array(
                    'editor_style'    => 'michigan-framework--accordion-ed-css',
                    'editor_script'   => 'michigan-framework--accordion-ed-js',
                    'render_callback' => function( $atts, $content ) {
                        self::$_accordions++;

                        // rendered HTML saved in database (old way)
                        if( strpos( $content, '{{ID}}' ) !== false ) {
                            $content = preg_replace(
                                '#<input([^>]*)/?>#',
                                '<input$1 aria-hidden="true" />',
                                $content
                            );

                            return str_replace(
                                array( ' state="opened"', ' state=""', '{{ID}}' ),
                                array( ' checked="checked"', '', self::$_accordions ),
                                $content
                            );
                        }
                        else {
                            $atts = array_merge(array(
                                'id'        => 'mfw-accordion-'. self::$_accordions,
                                'title'     => 'Accordion Title',
                                'state'     => '',
                                'className' => ''
                            ), $atts );

                            $templateVars = array(
                                '{{ID}}'        => self::$_accordions,
                                '{{BLOCK_ID}}'  => $atts['id'],
                                '{{STATE}}'     => ($atts['state'] == 'opened' ? 'checked="checked"' : null),
                                '{{TITLE}}'     => $atts['title'],
                                '{{CONTENT}}'   => $content,
                                '{{CLASSNAME}}' => $atts['className']
                            );

                            $template = '
                            <div class="wp-block-michigan-framework-accordion mfw-accordion {{CLASSNAME}}" id="{{BLOCK_ID}}">
                                <input id="mfw-accordion-action-{{ID}}" type="checkbox" {{STATE}}>
                                <label for="mfw-accordion-action-{{ID}}" role="heading" aria-level="6">
                                    <span class="mfw-accordion-title" id="mfw-accordion-action-button-{{ID}}" role="button" aria-controls="mfw-accordion-content-{{ID}}" aria-expanded="true">{{TITLE}}</span>
                                </label>
                                <div class="mfw-accordion-content-wrap transition" id="mfw-accordion-content-{{ID}}" role="region" aria-labelledby="mfw-accordion-action-button-{{ID}}" style="">
                                    <div class="mfw-accordion-content">{{CONTENT}}</div>
                                </div>
                            </div>
                            ';

                            return str_replace(
                                array_keys( $templateVars ),
                                array_values( $templateVars ),
                                $template
                            );
                        }
                    }
                ));
            }
        });


        // THEME UPDATE HOOKS
        add_filter( 'pre_set_site_transient_update_themes', 'MichiganFramework::_updateCheck' );
        add_filter( 'upgrader_source_selection', 'MichiganFramework::_updateSource', 10, 3 );
        if( is_admin() ) {
            get_transient( 'update_themes' );
        }

        // 10% chance of cleanup
        if( mt_rand( 1, 10 ) == 3 ) {
            add_action( 'shutdown', 'MichiganFramework::remoteImageCleanup' );
        }
    }

    /**
     * Override the settings in config.json for a specific template.
     * NOTE: only works to disable or resize.  Cannot enable something that is not globally enabled.
     **/
    static public function setConfig( $zone, $option, $flag, $value )
    {
        if( isset( self::$_config[ $zone ][ $option ][ $flag ] ) ) {
            self::$_config[ $zone ][ $option ][ $flag ] = $value;
        }
    }

    /**
     * Standard theme setup logic
     **/
    static public function setupTheme()
    {
        // Adds RSS feed links to <head> for posts and comments.
        add_theme_support( 'automatic-feed-links' );

        // image stuff
        add_theme_support( 'post-thumbnails' );

        // add thumbnail sizes
        foreach( self::$_config['thumbnails'] as $key => $thumb ) {
            if( $key == 'example-thumb-key' ) {
                continue;
            }

            add_image_size(
                $key,
                $thumb['width'],
                $thumb['height'],
                $thumb['crop']
            );
        }

        /**
         * This feature enables custom-menus support for a theme.
         * @see http://codex.wordpress.org/Function_Reference/register_nav_menus
         */
        foreach( self::$_config['menus'] as $key => $val ) {
            if( $val ) {
                register_nav_menu( $key, __( $val, 'michigan_framework' ) );
            }
        }

        add_theme_support('custom-header', array(
            // Header text display default
           'header-text'            => false,
            // Header image flex width
           'flex-width'             => true,
            // Header image width (in pixels)
           'width'                  => 300,
            // Header image flex height
           'flex-height'            => true,
            // Header image height (in pixels)
           'height'                 => 100
        ));
    }


    /**
     * Add debugging classes (as needed)
     **/
    static public function bodyClass( $classes )
    {
        // add grid class (for ie < 8 style removal)
        $classes[] = 'mfwGrid-'. (self::$_config['grid'] ?: 12);

        if( isset( self::$_config['debug']['enabled'] ) && self::$_config['debug']['enabled'] ) {
            $classes[] = 'mfwDebug';

            if( isset( self::$_config['debug']['grid'] ) && self::$_config['debug']['grid'] ) {
                $classes[] = 'mfwDebugGrid';
            }

            if( isset( self::$_config['debug']['widgets'] ) && self::$_config['debug']['widgets'] ) {
                $classes[] = 'mfwDebugWidgets';
            }
        }

        return $classes;
    }


    /**
     * Load base CSS/JS & autoload from styles/*.css and scripts/*.js for child themes
     **/
    static public function enqueue()
    {
        // vendor assets
        wp_enqueue_style( 'foundation-base', PARENT_URL .'/vendor/foundation-'. self::$_foundationVersion .'/'. self::$_config['grid'] .'col/foundation.min.css', null, self::$_foundationVersion );

        wp_enqueue_style( 'font-awesome', PARENT_URL .'/vendor/font-awesome-'. self::$_fontAwesomeVersion .'/css/font-awesome.min.css', null, self::$_fontAwesomeVersion );
        wp_enqueue_script( 'jq-placeholder', PARENT_URL .'/vendor/scripts/jquery.placeholder.js', array( 'jquery' ), self::$_version );

        wp_enqueue_style( 'mfw-base', PARENT_URL .'/styles/base.css', null, self::$_version );
        wp_enqueue_script( 'mfw', PARENT_URL .'/scripts/mfw.js', array( 'jquery' ), self::$_version );

        // debug assets
        if( self::$_config['debug']['enabled'] ) {
            wp_enqueue_style( 'mfw-debug', PARENT_URL .'/styles/mfw-debug.css', null, self::$_version );
            wp_enqueue_script( 'mfw-debug', PARENT_URL .'/scripts/mfw-debug.js', array( 'jquery' ), self::$_version );
        }

        // autoload child styles/scripts from styles/scripts directories
        if( PARENT_DIR !== CHILD_DIR ) {
            $files = array();
            foreach( glob( CHILD_DIR . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR .'*.css' ) as $file ) {
                $files[ preg_replace( '/\.css$/i', '', basename( $file ) ) ] = $file;
            }
            ksort( $files );
            foreach( $files as $key => $file ) {
                $file = str_replace( CHILD_DIR, '', $file );
                wp_enqueue_style( 'child-'. $key, CHILD_URL . $file, null, filemtime( CHILD_DIR . $file ) );
            }

            $files = array();
            foreach( glob( CHILD_DIR . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR .'*.js' ) as $file ) {
                $files[ preg_replace( '/\.js$/i', '', basename( $file ) ) ] = $file;
            }
            ksort( $files );
            foreach( $files as $key => $file ) {
                $file = str_replace( CHILD_DIR, '', $file );
                wp_enqueue_script( 'child-'. $key, CHILD_URL . $file, array( 'jquery' ), filemtime( CHILD_DIR . $file ) );
            }
        }
    }

    /**
     * ADD HEADER IE CONDITIONALS
     **/
    static public function wpHead()
    {
        echo '
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="'. PARENT_URL .'/vendor/foundation-ie8/'. self::$_config['grid'] .'col.css" />
            <link rel="stylesheet" href="'. PARENT_URL .'/styles/base-ie8.css" />
        <![endif]-->
        ';
    }

    /**
     * ADD FOOTER IE CONDITIONALS
     **/
    static public function wpFooter()
    {
        echo '
        <!--[if lt IE 9]>
            <script src="'. PARENT_URL .'/vendor/scripts/rem.js" type="text/javascript"></script>
        <![endif]-->
        ';

    }

    /**
     * Add grid overlay html
     */
    static public function getGridOverlay() {
        if( self::$_config['debug']['enabled'] ) {
            echo '
            <div id="mfwGridOverlay">
                <div class="row">
                ';

                for( $i=0; $i < self::$_config['grid']; $i++ ) {
                echo '    <div class="small-1 columns"><div class="mfwGridBackground"></div></div>
                ';
                }
            echo '
                </div>
            </div>
            ';
        }
    }

    // EXCERPT MORE LINK
    static public function excerptMore( $more )
    {
        global $post;

        return ' <a href="'. get_permalink( $post->ID ) .'" class="readmore">Read more</a>';
    }

    // check if the archive paginates
    static public function willPaginate()
    {
        global $wp_query;

        if( !is_singular() && ($wp_query->max_num_pages > 1) ) {
            return true;
        }

        return false;
    }

    /**
     * Add admin bar options if debug enabled
     **/
    static public function adminBarRender()
    {
        global $wp_admin_bar;

        if( self::$_config['debug']['enabled'] ) {
            // root menu
            $wp_admin_bar->add_menu(array(
                'parent' => false,
                'id'     => 'mfw-admin-root',
                'title'  => 'Michigan Framework',
                'href'   => false
            ));

            // submenu item
            $wp_admin_bar->add_menu(array(
                'parent' => 'mfw-admin-root',
                'id'     => 'toggle-widgets',
                'title'  => self::$_config['debug']['widgets'] ? 'Hide Widget Areas' : 'Show Widget Areas',
                'href'   => '#',
                'meta'   => array(
                    'onclick' => 'return mfwToggleWidgets();'
                )
            ));

            $wp_admin_bar->add_menu(array(
                'parent' => 'mfw-admin-root',
                'id'     => 'toggle-grid',
                'title'  => self::$_config['debug']['grid'] ? 'Hide Grid' : 'Show Grid',
                'href'   => '#',
                'meta'   => array(
                    'onclick' => 'return mfwToggleGrid();'
                )
            ));
        }
    }


    /**
     * Add admin bar options if debug enabled
     **/
    static public function adminImageSizes( $sizes )
    {
        foreach( self::$_config['thumbnails'] as $key => $thumb ) {
            if( ($key == 'example-thumb-key') || (isset( $thumb['admin'] ) && !$thumb['admin']) ) {
                continue;
            }

            $sizes[ $key ] = isset( $thumb['name'] ) ? $thumb['name'] : ucwords( str_replace( '-', ' ', $key ) );
        }

        return $sizes;
    }


    /**
     * Check if an area is enabled or not
     **/
    static public function areaEnabled( $config )
    {
        $debug = 'mfwWidget';

        if( strpos( $config, ',' ) ) {
            $return      = false;
            $allDisabled = true;
            foreach( explode( ',', $config ) as $config ) {
                if( ($tmp = self::areaEnabled( $config )) !== false ) {
                    // part disabled by config ignore it
                    if( $tmp === false ) {
                        continue;
                    }

                    // part of the group is enabled
                    if( ($tmp === '') || ($tmp === $debug) ) {
                        $allDisabled = false;
                    }

                    // if this is the debug class then append "Group"
                    // if $debug is returned then set this to $debug.Group
                    if( (strpos( $tmp, $debug ) === 0) ) {
                        $tmp = $debug.'Group';
                    }

                    // if enabled has not been set
                    if( !$return ) {
                        $return = $tmp;
                    }
                }
            }

            if( $allDisabled && self::$_config['debug']['enabled'] ) {
                $return .= ' mfwInactive';
            }

            return $return;
        }
        else {
            list( $zone, $location ) = explode( ':', $config );

            $return = false;

            // if enabled in config
            if( isset( self::$_config[ $zone ][ $location ]['enabled'] ) && self::$_config[ $zone ][ $location ]['enabled'] ) {
                // if we have widgets configured
                if( is_active_sidebar( $location ) ) {
                    return $debug; // show this area
                }
                else if( self::$_config['debug']['enabled'] ) {
                    return $debug . ' mfwInactive';
                }
            }

            return $return;
        }
    }

    /**
     * Get columns for a location
     **/
    static public function getColumns( $config, $default = null )
    {
        $defaultColumns = (self::$_config['grid'] ?: 12);

        $columns = $default ? $default : array( 'large' => $defaultColumns );

        list( $zone, $location ) = explode( ':', $config );

        if( isset( self::$_config[ $zone ][ $location ]['columns'] ) ) {
            $columns = self::$_config[ $zone ][ $location ]['columns'];
        }

        $return = null;
        foreach( $columns as $key => $val ) {
            $val = $val ?: $defaultColumns;

            $return[] = "{$key}-{$val}";
        }

        return implode( ' ', apply_filters( 'mfw-getcolumns', $return, $config, $default ) );
    }

    /**
     * Calculate and get columns for main content well
     **/
    static public function getContentColumns()
    {
        $defaultColumns = (self::$_config['grid'] ?: 12);

        $columns = array(
            'small'  => $defaultColumns,
            'medium' => $defaultColumns,
            'large'  => $defaultColumns
        );

        $widgets = isset( self::$_config['content']['content_main']['widgets'] )
                 ? self::$_config['content']['content_main']['widgets'] : array();

        foreach( $widgets as $widget ) {
            if( self::areaEnabled( 'widgets:'. $widget ) !== false ) {
                $columns['small']  -= self::$_config['widgets'][ $widget ]['columns']['small'];
                $columns['medium'] -= self::$_config['widgets'][ $widget ]['columns']['medium'];
                $columns['large']  -= self::$_config['widgets'][ $widget ]['columns']['large'];
            }
        }

        $return = null;
        foreach( $columns as $key => $val ) {
            if( $val < 1 ) {
                $val = $defaultColumns;
            }

            $return[] = "{$key}-{$val}";
        }

        return implode( ' ', apply_filters( 'mfw-getcontentcolumns', $return ) );
    }

    /**
     * Check if widget is enabled and display with wrapping element w/grid classes
     */
    static public function displayWidget( $widget, $alwaysShow = true )
    {
        if( !($status = self::areaEnabled( 'widgets:'. $widget )) ) {
            return;
        }

        $alwaysShow = apply_filters( 'mfw-displaywidget_always-show', $alwaysShow );

        ob_start();
        dynamic_sidebar( $widget );
        $html = ob_get_clean();

        if( $alwaysShow || $html ) {
            echo '<div id="widget-'. $widget .'" class="'. self::getColumns( 'widgets:'. $widget ) .' columns '.( is_string( $status ) ? $status : null).'">'. $html .'</div>';
        }
        else {
            self::setConfig( 'widgets', $widget, 'enabled', 0 );
        }
    }

    /**
     * Join on postmeta table for search
     */
    static public function searchJoin( $join )
    {
        global $wpdb;

        if( is_search() ) {
            $keys = 0;

            foreach( self::$_config['search']['meta-keys'] as $key ) {
                if( $key == 'example-meta-key' ) {
                    continue;
                }

                $keys++;
            }

            if( $keys ) {
                $join .= "
                LEFT JOIN {$wpdb->prefix}postmeta searchmeta
                  ON searchmeta.post_id = {$wpdb->posts}.ID
                ";
            }
        }

        return $join;
    }

    /**
     * Add custom meta fields to search
     */
    static public function searchWhere( $where )
    {
        // @NOTE: see https://wordpress.org/support/topic/include-custom-field-values-in-search#post-1932930 by David C
        if( is_search() && isset( self::$_config['search']['meta-keys'] )  ) {
            $tWhere   = array();
            $tWhere[] = '$0'; // put back what we are "replacing"

            // load in new meta fields to search against
            foreach( self::$_config['search']['meta-keys'] as $key ) {
                if( $key == 'example-meta-key' ) {
                    continue;
                }

                $tWhere[] = "((searchmeta.meta_key = '{$key}') AND (searchmeta.meta_value  LIKE $2))";
            }

            // update WHERE clause
            $where = preg_replace(
                "/\(([^(]*?)post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                implode( ' OR ', $tWhere ),
                $where
            );
        }

        return $where;
    }

    /**
     * Only match on distinct post results
     */
    static public function searchDistinct()
    {
        if( is_search() && !self::$_config['search']['duplicates'] ) {
            return 'DISTINCT';
        }

        return '';
    }

    /**
     * Add other file types to be uploaded
     */
    static public function customUploadTypes( $mimes )
    {
        if( self::$_config['uploads']['mimes'] ) {
            foreach( self::$_config['uploads']['mimes'] as $key => $mime ) {
                if( $mime && !isset( $mimes[ $key ] ) ) {
                    $mimes[ $key ] = $mime;
                }
                else {
                    unset( $mimes[ $key ] );
                }
            }
        }

        return $mimes;
    }

    /**
     * Accordion Shortcode
     */
    static public function shortcodeAccordion( $atts, $content = null )
    {
        self::$_accordions++;

        $atts = shortcode_atts(array(
            'title' => 'ACCORDION NEEDS TITLE ATTRIBUTE',
            'id'    => 'mfw-accordion-'. self::$_accordions,
            'class' => '',
            'state' => ''
        ), $atts );
        $atts['title'] = $atts['title'] ?: 'ACCORDION NEEDS TITLE ATTRIBUTE';

        return '
        <div id="'. $atts['id'] .'" class="mfw-accordion '. $atts['class'] .'">
            <input id="mfw-accordion-action-'. self::$_accordions .'" type="checkbox" '. ($atts['state'] == 'opened' ? 'checked="checked"' : null) .' aria-hidden="true" />
            <label for="mfw-accordion-action-'. self::$_accordions .'" role="heading" aria-level="6"><span id="mfw-accordion-action-button-'. self::$_accordions .'" role="button" aria-controls="mfw-accordion-content-'. self::$_accordions .'" aria-expanded="'. ($atts['state'] == 'opened' ? 'true' : 'false' ) .'">'. $atts['title'] .'</span></label>
            <div id="mfw-accordion-content-'. self::$_accordions .'" class="mfw-accordion-content-wrap" role="region" aria-labelledby="mfw-accordion-action-button-'. self::$_accordions .'"><div class="mfw-accordion-content">'. do_shortcode( $content ) .'</div></div>
        </div>
        ';
    }

    static public function shortcodeEmptyParagraphFix( $content )
    {
        // define your shortcodes to filter, '' filters all shortcodes
        $shortcodes = apply_filters( 'mfw-shortcode-paragraphfix', array( 'accordion' ) );

        foreach( $shortcodes as $shortcode ) {
            $array = array (
                '<p>[' . $shortcode => '[' .$shortcode,
                '<p>[/' . $shortcode => '[/' .$shortcode,
            );
            $content = strtr( $content, $array );

            $array = array(
                $shortcode .'(.*?)\]<\/p>' => $shortcode . '$1]',
                $shortcode .'(.*?)\]<br \/>' => $shortcode . '$1]'
            );
            foreach( $array as $search => $replace ) {
                $content = preg_replace( '/'. $search .'/', $replace, $content );
            }
        }

        return $content;
    }


    static public function remoteImageThumb( $imageUrl, $size = 'full', $crop = null, $path = '', $expires = -1 )
    {
        global $_wp_additional_image_sizes;

        $expires === -1 ? (60 * 60 * 24 * 7) : $expires;

        if( strpos( $src, 'mfw-image-cache/' ) !== false ) {
            return $src;
        }

        // prepare thumbnail destination
        $wpUpload = wp_upload_dir();
        $tmp = array(
            $wpUpload['basedir'],
            'mfw-image-cache',
        );
        if( $path ) {
            $tmp[] = $path;
            $path .= '/';
        }
        $cachePath = implode( DIRECTORY_SEPARATOR, $tmp );

        // get width/height by thumbnail size
        if( !is_array( $size ) ) {
            if( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
                $width  = get_option( $size .'_size_w' );
                $height = get_option( $size .'_size_h' );
                $crop   = is_null( $crop ) ? (get_option( $size .'_crop', null )) : $crop;
                $crop   = is_null( $crop ) ? true : (bool) $crop;
            }
            else if( isset( $_wp_additional_image_sizes[ $size ] ) ) {
                $width  = $_wp_additional_image_sizes[ $size ]['width'];
                $height = $_wp_additional_image_sizes[ $size ]['height'];
                $crop   = is_null( $crop ) ? $_wp_additional_image_sizes[ $size ]['crop'] : $crop;
            }
            // we don't know what the width/height of this is
            else {
                return $imageUrl;
            }
        }
        else {
            list( $width, $height ) = $size;
            $crop = is_null( $crop ) ? true : $crop;
        }                                                                                                             


        // check if we already have the image cached and cache is still good
        //$info = pathinfo( $imageUrl );
        $info = array(
            'filename'  => md5( $imageUrl ),
            'extension' => preg_replace( '/^\./', '', image_type_to_extension( exif_imagetype( $imageUrl ) ) )
        );
        $info['extension'] = str_replace( 'jpeg', 'jpg', $info['extension'] );

        if( !$info['filename'] || !$info['extension'] ) {
            return $imageUrl;
        }

        $cacheFile = $cachePath . DIRECTORY_SEPARATOR ."{$info['filename']}-{$width}x{$height}.{$info['extension']}";

        if( file_exists( $cacheFile ) && ((filemtime( $cacheFile ) + $expires) > time()) ) {
            return $wpUpload['baseurl'] .'/mfw-image-cache/'. $path . basename( $cacheFile );
        }


        // CACHE DNE OR IS STALE SO LETS REDO IT

        // prevent race condition on updating image
        if( file_exists( $cacheFile ) ) {
            @touch( $cacheFile );
        }

        // prepare editor/load remote image
        $img = wp_get_image_editor( $imageUrl );
        if( is_wp_error( $img ) || ($size == 'full') ) {
            return $imageUrl;
        }

        // resize image
        $img->resize( $width, $height, $crop );

        // make storage directory
        wp_mkdir_p( $cachePath );

        // save image
        $thumb = $img->save( $cacheFile );

        if( is_wp_error( $thumb ) || !isset( $thumb['path'] ) ) {
            return $imageUrl;
        }

        return $wpUpload['baseurl'] .'/mfw-image-cache/'. $path . $thumb['file'] .'?time='. time();
    }

    static public function remoteImageCleanup( $dir = null, $expires = -1, $recursive = true )
    {
        $expires === -1 ? (60 * 60 * 24 * 30) : $expires;

        if( !$dir ) {
            $wpUpload = wp_upload_dir();
            $tmp = array(
                $wpUpload['basedir'],
                'mfw-image-cache'
            );

            $dir = implode( DIRECTORY_SEPARATOR, $tmp );
        }

        foreach( glob( $dir . DIRECTORY_SEPARATOR .'*' ) as $file ) {
            if( is_dir( $file ) ) {
                if( $recursive ) {
                    self::_remoteImageCleanup( $file, $expires, $recursive );
                }
            }
            else if( (filemtime( $file ) + $expires) < time() ) {
                unlink( $file );
            }
        }
    }


    /**
     * Check for updated version
     **/
    static public function _updateCheck( $checkedData )
    {
        $raw = wp_remote_get(
            trailingslashit( self::$_gitUpdate['raw'] ) .'style.css',
            array( 'sslverify' => true )
        );

        if( !is_array( $raw ) || !isset( $raw['body'] ) ) {
            return $checkedData;
        }

        if( preg_match( '#^\s*Version\:\s*(.*)$#im', $raw['body'], $matches ) ) {
            $version = $matches[1];
        }

        if( $version && version_compare( $version, self::$_version ) ) {
            $checkedData->response[ self::$_gitUpdate['dir'] ] = array(
                'package'     => self::$_gitUpdate['zip'],
                'new_version' => $version,
                'url'         => self::$_gitUpdate['url']
            );
        }

        return $checkedData;
    }

    /**
     * Rename downloaded source directory
     **/
    static public function _updateSource( $source, $remote, $upgrader )
    {
        global $wp_filesystem;

        // check for upgrade process
        if( !is_a( $upgrader, 'Theme_Upgrader' ) || !isset( $source, $remote ) ) {
            return $source;
        }

        // check if this theme is being updated
        if( stripos( basename( $source ), basename( self::$_gitUpdate['url'] ) ) === false ) {
            return $source;
        }

        // Rename source
        $destination = trailingslashit( $remote ) . self::$_gitUpdate['dir'];
        if( $wp_filesystem->move( $source, $destination, true ) ) {
            return $destination;
        }

        return new WP_Error();
    }
}
MichiganFramework::init();
