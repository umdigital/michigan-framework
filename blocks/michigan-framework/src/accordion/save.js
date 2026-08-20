import { __ } from '@wordpress/i18n';

import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	return (
        <InnerBlocks.Content />
	);
}
