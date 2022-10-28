<?php

class Theme_MichiganFramework_Block_Accordion
{
    static private $_prefix = 'michigan-framework';
    static private $_block  = 'accordion';

    static private $_accordions = 0;

    static public function init()
    {
        wp_register_style(
            self::$_prefix .'--'. self::$_block .'-ed-css',
            PARENT_URL . '/blocks/'. self::$_block .'/editor.css',
            array(),
            filemtime( PARENT_DIR . '/blocks/'. self::$_block .'/editor.css' )
        );
        wp_register_script(
            self::$_prefix .'--'. self::$_block .'-ed-js',
            PARENT_URL . '/blocks/'. self::$_block .'/editor.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( PARENT_DIR . '/blocks/'. self::$_block .'/editor.js' )
        );

        register_block_type( self::$_prefix .'/'. self::$_block, array(
            'editor_style'    => self::$_prefix .'--'. self::$_block .'-ed-css',
            'editor_script'   => self::$_prefix .'--'. self::$_block .'-ed-js',
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

                return self::_display( $atts, $content );
            }
        ));

        // add legacy shortcode option
        add_filter( 'mfw-shortcode-paragraphfix', function( $shortcodes ){
            return array_merge(
                $shortcodes, array(
                    'accordion'
                )
            );
        });

        add_shortcode( 'accordion', function( $atts, $content = null ){
            self::$_accordions++;

            $atts = array_merge(array(
                'title' => 'Accordion Title: Needs Attribute'
            ), $atts );

            if( isset( $atts['class'] ) ) {
                $atts['className'] = $atts['class'];
                unset( $atts['class'] );
            }

            if( function_exists( 'do_shortcode' ) ) {
                $content = do_shortcode( $content );
            }

            return self::_display( $atts, $content );
        });
    }

    static private function _display( $config, $content )
    {
        $config = array_merge(array(
            'id'        => 'mfw-accordion-'. self::$_accordions,
            'title'     => 'Accordion Title',
            'state'     => '',
            'className' => '',
        ), $config );

        $templateVars = array(
            '{{ID}}'        => self::$_accordions,
            '{{BLOCK_ID}}'  => $config['id'],
            '{{STATE}}'     => ($config['state'] == 'opened' ? 'checked="checked"' : null),
            '{{TITLE}}'     => $config['title'],
            '{{CONTENT}}'   => $content,
            '{{CLASSNAME}}' => $config['className']
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
Theme_MichiganFramework_Block_Accordion::init();
