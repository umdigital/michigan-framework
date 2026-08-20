import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import save from './save';
import deprecated from './deprecated';
import { transforms } from './transforms';
import metadata from './block.json';

registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,

    deprecated,

    transforms,
} );
