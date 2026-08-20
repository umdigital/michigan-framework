<?php

/** @NOTES:
    - need to figure out why the enque urls are including the full pathname when the theme is used in the manner um2022 uses it
    - not sure if we need to manually load the assets vs using the block.json or manifest
*/

class Theme_MichiganFramework_Block_Accordion
{
    static private $_accordions = 0;

    static public function init()
    {
        if( function_exists('register_block_type') ) {
            wp_register_style(
                'michigan-framework--accordion--css',
                PARENT_URL .'/blocks/michigan-framework/build/accordion/style-index.css',
                [],
                filemtime( PARENT_DIR .'/blocks/michigan-framework/build/accordion/style-index.css' )
            );
            wp_register_style(
                'michigan-framework--accordion--ed-css',
                PARENT_URL .'/blocks/michigan-framework/build/accordion/index.css',
                [],
                filemtime( PARENT_DIR .'/blocks/michigan-framework/build/accordion/index.css' )
            );
            wp_register_script(
                'michigan-framework--accordion--ed-js',
                PARENT_URL .'/blocks/michigan-framework/build/accordion/index.js',
                [],
                filemtime( PARENT_DIR .'/blocks/michigan-framework/build/accordion/index.js' )
            );
            register_block_type( 'michigan-framework/accordion', [
                'api_version'            => 3,
                'title'                  => 'U-M Accordion',
                'description'            => 'Accordion',
                'icon'                   => 'sort',
                'category'               => 'layout',
                'attributes'             => [
                    'title' => [
                        'type'     => 'string',
                        'selector' => 'span'
                    ],
                    'id' => [
                        'type' => 'string',
                    ],
                    'state' => [
                        'type' => 'string',
                    ]
                ],
                'style_handles'         => ['michigan-framework--accordion--css'],
                'editor_style_handles'  => ['dashicons','michigan-framework--accordion--ed-css'],
                'editor_script_handles' => ['michigan-framework--accordion--ed-js'],
                'render_callback'       => function( $attributes, $content ){
                    self::$_accordions++;

                    // rendered HTML saved in database (old way)
                    if( strpos( $content, '{{ID}}' ) !== false ) {
                        if( preg_match( '#value="(.*?)"#', $content, $match ) ) {
                            $attributes['title'] = $match[1];
                        }

                        if( preg_match( '#<div class="mfw-accordion-content">(.*?)</div>#ms', $content, $match ) ) {
                            $content = $match[1];
                        }
                    }

                    ob_start();
                    include( PARENT_DIR .'/blocks/michigan-framework/build/accordion/render.php' );
                    return ob_get_clean();
                }
            ]);
        }

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

            $atts = shortcode_atts(array(
                'title' => 'Accordion Title: Needs Attribute'
            ), $atts );

            if( isset( $atts['class'] ) ) {
                $atts['className'] = $atts['class'];
                unset( $atts['class'] );
            }

            if( function_exists( 'do_shortcode' ) ) {
                $content = do_shortcode( $content );
            }

            return self::display( $atts, $content );
        });
    }

    static public function display( $config, $content )
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
