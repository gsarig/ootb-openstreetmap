/**
 * BLOCK: openstreetmap
 *
 */
import './index.css';
import './view.css';
// noinspection NpmUsedModulesInstalled
import {registerBlockType} from '@wordpress/blocks';
import metadata from './block.json';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';
import deprecated from './deprecated';

registerBlockType(metadata, {
	edit,
	save,
	deprecated,
});
