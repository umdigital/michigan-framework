import { __ } from '@wordpress/i18n';

import { useBlockProps, InnerBlocks, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	return (
        <>
            <InspectorControls>
                <PanelBody title="Accordion Options" initialOpen={ true }>
                    <TextControl
                        label='Custom ID'
                        help='ID attribute of accordion. Helpful for anchoring to a specific accordion'
                        value={ attributes.id }
                        onChange={function( value ){
                            value = value.toLowerCase();
                            value = value.replace( /[^a-z0-9-]/, '-' );
                            setAttributes({ id: value });
                        }}
                    />
                    <SelectControl
                        label='Default State'
                        value={ attributes.state }
                        options={[{
                                value: '',
                                label: 'Collapsed'
                            },{
                                value: 'opened',
                                label: 'Expanded'
                        }]}
                        onChange={ (value) => setAttributes({ state: value }) }
                    />
                </PanelBody>
            </InspectorControls>

            <div { ...useBlockProps({className: 'mfw-accordion'}) }>
                <label>
                    <RichText
                        className='mfw-accordion-title'
                        inline={ true }
                        value={ attributes.title }
                        placeholder='Accordion Title...'
                        keepPlaceholderOnFocus={ true }
                        onChange={ (value) => setAttributes({ title: value }) }
                />
                </label>
                <div className='mfw-accordion-content-wrap'>
                    <div className='mfw-accordion-content'>
                        <InnerBlocks
                            template={[
                                ['core/paragraph', {
                                    placeholder: 'Accordion Content',
                                    keepPlaceholderOnFocus: true
                                }]
                            ]}
                        />
                    </div>
                </div>
            </div>
        </>
	);
}
