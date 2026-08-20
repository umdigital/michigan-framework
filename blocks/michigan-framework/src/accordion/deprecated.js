import { __ } from '@wordpress/i18n';

import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

const v1 = {
    apiVersion: 1,
    attributes: {
        title: {
            type: 'string',
            source: 'attribute',
            selector: 'span',
            attribute: 'value'
        },
        id: {
            type: 'string'
        },
        state: {
            type: 'string'
        }
    },

    save: function({ attributes }){
        return (
            <div { ...useBlockProps.save({
                className: "mfw-accordion",
                id: (attributes.id ? attributes.id : 'mfw-accordion-{{ID}}')
            }) }>
                <input 
                    id="mfw-accordion-action-{{ID}}"
                    type="checkbox"
                    state={ attributes.state }
                />
                <label
                    htmlFor="mfw-accordion-action-{{ID}}"
                    role="heading"
                    aria-level="6"
                >
                    <span
                        className="mfw-accordion-title"
                        id="mfw-accordion-action-button-{{ID}}"
                        role="button"
                        aria-controls="mfw-accordion-content-{{ID}}"
                        aria-expanded={(attributes.state == 'opened' ? 'true' : 'false')}
                        value={(attributes.title ? attributes.title : 'ACCORDION NEEDS TITLE ATTRIBUTE')}
                    />
                </label>
                <div
                    className="mfw-accordion-content-wrap"
                    id="mfw-accordion-content-{{ID}}"
                    role="region"
                    aria-labelledby="mfw-accordion-action-button-{{ID}}"
                >
                    <div className="mfw-accordion-content">
                        <InnerBlocks.Content />
                    </div>
                </div>
            </div>
        );
    }
}

export default[ v1 ];
