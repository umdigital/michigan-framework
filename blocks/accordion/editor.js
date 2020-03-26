(function() {
    var __ = wp.i18n.__;

    wp.blocks.registerBlockType( 'michigan-framework/accordion', {
        title   : __( 'Accordion', 'michigan-framework' ),
        icon    : 'sort',
        category: 'layout',

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

        edit: function( props ){
            return [
                wp.element.createElement(
                    wp.element.Fragment,
                    null,
                    wp.element.createElement(
                        wp.blockEditor.InspectorControls,
                        null,
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
                                    allowedBlocks: [
                                        'core/image',
                                        'core/heading',
                                        'core/list',
                                        'core/paragraph',
                                        'core/quote',
                                        'core/table'
                                    ],
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
        }
    });
}());
