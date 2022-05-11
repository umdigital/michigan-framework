(function() {
    var __ = wp.i18n.__;

    wp.blocks.registerBlockType( 'michigan-framework/accordion', {
        title   : __( 'Accordion', 'michigan-framework' ),
        icon    : 'sort',
        category: 'layout',

        attributes: {
            title: {
                type: 'string',
                selector: 'span'
            },
            id: {
                type: 'string'
            },
            state: {
                type: 'string'
            }
        },

        edit: function( props ){
            return [
                wp.element.createElement(
                    wp.element.Fragment,
                    null,
                    wp.element.createElement(
                        wp.editor.InspectorControls,
                        null,
                        wp.element.createElement(
                            wp.components.PanelBody, {
                                title: 'Accordion Options',
                                initialOpen: true
                            },
                            wp.element.createElement(
                                wp.components.TextControl, {
                                    label: 'Custom ID',
                                    help: 'ID attribute of accordion. Helpful for anchoring to a specific accordion',
                                    value: props.attributes.id,
                                    onChange: function( value ) {
                                        value = value.toLowerCase();
                                        value = value.replace( /[^a-z0-9-]/, '-' );
                                        props.setAttributes({
                                            id: value
                                        });
                                    }
                                }
                            ),
                            wp.element.createElement(
                                wp.components.SelectControl, {
                                    label: 'Default State',
                                    value: props.attributes.state,
                                    options: [
                                        {
                                            value: '',
                                            label: 'Collapsed',
                                        },
                                        {
                                            value: 'opened',
                                            label: 'Expanded'
                                        }
                                    ],
                                    onChange: function( value ){
                                        props.setAttributes({
                                            state: value
                                        });
                                    }
                                }
                            )
                        )
                    ),

                    wp.element.createElement( 'div', {
                            className: 'mfw-accordion'
                        },
                        wp.element.createElement( 'label', {},
                            wp.element.createElement( wp.blockEditor.RichText, {
                                className  : 'mfw-accordion-title',
                                inline     : true,
                                value      : props.attributes.title,
                                placeholder: 'Accordion Title...',
                                keepPlaceholderOnFocus: true,
                                onChange   : function( value ){
                                    props.setAttributes({
                                        title: value
                                    });
                                }
                            }),
                        ),
                        wp.element.createElement( 'div', {
                                className: 'mfw-accordion-content-wrap',
                            },
                            wp.element.createElement( 'div', {
                                    className: 'mfw-accordion-content'
                                },
                                wp.element.createElement( wp.blockEditor.InnerBlocks, {
                                    template: [
                                        ['core/paragraph', {
                                            placeholder: 'Accordion Content',
                                            keepPlaceholderOnFocus: true
                                        }]
                                    ]
                                })
                            )
                        )
                    )
                )
            ];
        },

        save: function( props ){
            return wp.element.createElement( wp.blockEditor.InnerBlocks.Content, {} );
        },

        deprecated: [{
            attributes: {
                title: {
                    type: 'string',
                    source: 'text',
                    selector: 'span'
                },
                id: {
                    type: 'string'
                },
                state: {
                    type: 'string'
                }
            },

            save: function( props ){
                return (
                    wp.element.createElement( 'div', {
                            className: 'mfw-accordion',
                            id       : (props.attributes.id ? props.attributes.id : 'mfw-accordion-{{ID}}')
                        },
                        wp.element.createElement( 'input', {
                            id   : 'mfw-accordion-action-{{ID}}',
                            type : 'checkbox',
                            state: props.attributes.state,
                        }),
                        wp.element.createElement( 'label', {
                                'for': 'mfw-accordion-action-{{ID}}',
                                role : 'heading',
                                'aria-level': '6'
                            },
                            wp.element.createElement( wp.blockEditor.RichText.Content, {
                                tagName  : 'span',
                                className: 'mfw-accordion-title',
                                id       : 'mfw-accordion-action-button-{{ID}}',
                                role     : 'button',
                                value    : (props.attributes.title ? props.attributes.title : 'ACCORDION NEEDS TITLE ATTRIBUTE'),
                                'aria-controls': 'mfw-accordion-content-{{ID}}',
                                'aria-expanded': (props.attributes.state == 'opened' ? 'true' : 'false')
                            }),
                        ),
                        wp.element.createElement( 'div', {
                                className: 'mfw-accordion-content-wrap',
                                id: 'mfw-accordion-content-{{ID}}',
                                role: 'region',
                                'aria-labelledby': 'mfw-accordion-action-button-{{ID}}'
                            },
                            wp.element.createElement( 'div', {
                                    className: 'mfw-accordion-content'
                                },
                                wp.element.createElement( wp.blockEditor.InnerBlocks.Content, null )
                            )
                        )
                    )
                );
            },
        }],

        transforms: {
            from:[{
                type: 'block',
                blocks: ['core/shortcode'],
                isMatch: function({ text }) {
                    return /^\[accordion /.test(text);
                },
                transform: ({ text }) => {
                    console.log( 'transforming accordion' );
                    const thisTitle   = getAttributeValue( 'accordion', 'title', text );
                    const thisID      = getAttributeValue( 'accordion', 'id',    text );
                    const thisState   = getAttributeValue( 'accordion', 'state', text );
                    const thisContent = getInnerContent( 'accordion', text );

                    const innerBlocks = wp.blocks.rawHandler({
                        HTML: thisContent
                    });

                    return wp.blocks.createBlock( 'michigan-framework/accordion', {
                        title: thisTitle,
                        id   : thisID,
                        state: thisState
                    }, innerBlocks );
                }
            }]
        /* @NOTE: This leaves the inner content out.  Not sure there is currently a way to handle it.  The above is a workaround/hack
                  per: https://www.bates.edu/webtech/2019/10/07/switching-from-shortcodes-to-blocks-part2/
            from: [{
                type: 'shortcode',
                tag : 'accordion',
                attributes: {
                    title: {
                        type: 'string',
                        shortcode: ({ named: { title } }) => {
                            return title;
                        }
                    },
                    id: {
                        type: 'string',
                        shortcode: ({ named: { id } }) => {
                            return id;
                        }
                    },
                    state: {
                        type: 'string',
                        shortcode: ({ named: { state } }) => {
                            return state;
                        }
                    }
                }
            }]
        */
        }
    });

    /**
     * Get the value for a shortcode attribute, whether it's enclosed in double quotes, single
     * quotes, or no quotes.
     * @SOURCE: https://www.bates.edu/webtech/2019/10/07/switching-from-shortcodes-to-blocks-part2/
     * 
     * @param  {string} tag     The shortcode name
     * @param  {string} att     The attribute name
     * @param  {string} content The text which includes the shortcode
     *                          
     * @return {string}         The attribute value or an empty string.
     */
    const getAttributeValue = function(tag, att, content){
        // In string literals, slashes need to be double escaped
        // 
        //    Match  attribute="value"
        //    \[tag[^\]]*      matches opening of shortcode tag 
        //    att="([^"]*)"    captures value inside " and "
        var re = new RegExp(`\\[${tag}[^\\]]* ${att}="([^"]*)"`, 'im');
        var result = content.match(re);
        if( result != null && result.length > 0 )
            return result[1];

        //    Match  attribute='value'
        //    \[tag[^\]]*      matches opening of shortcode tag 
        //    att="([^"]*)"    captures value inside ' and ''
        re = new RegExp(`\\[${tag}[^\\]]* ${att}='([^']*)'`, 'im');
        result = content.match(re);
        if( result != null && result.length > 0 )
            return result[1];

        //    Match  attribute=value
        //    \[tag[^\]]*      matches opening of shortcode tag 
        //    att="([^"]*)"    captures a shortcode value provided without 
            //                     quotes, as in [me color=green]
        re = new RegExp(`\\[${tag}[^\\]]* ${att}=([^\\s]*)\\s`, 'im');
        result = content.match(re);
        if( result != null && result.length > 0 )
           return result[1];
        return false;
    };

    /**
     * Get the inner content of a shortcode, if any.
     * @SOURCE: https://www.bates.edu/webtech/2019/10/07/switching-from-shortcodes-to-blocks-part2/
     * 
     * @param  {string} tag         The shortcode tag
     * @param  {string} content      The text which includes the shortcode. 
     * @param  {bool}   shouldAutoP  Whether or not to filter return value with autop
     * 
     * @return {string}      An empty string if no inner content, or if the
     *                       shortcode is self-closing (no end tag). Otherwise
     *                       returns the inner content.
     */
    const getInnerContent = function(tag, content, shouldAutoP=true) {
       //   \[tag[^\]]*?]    matches opening shortcode tag with or without attributes, (not greedy)
       //   ([\S\s]*?)       matches anything in between shortcodes tags, including line breaks and other shortcodes
       //   \[\/tag]         matches end shortcode tag
       // remember, double escaping for string literals inside RegExp
       const re = new RegExp(`\\[${tag}[^\\]]*?]([\\S\\s]*?)\\[\\/${tag}]`, 'i');
       var result = content.match(re);
       if( result == null || result.length < 1 )
          return '';

       if( shouldAutoP == true)
          result[1] = wp.autop.autop(result[1]);

       return result[1];
    };
}());
