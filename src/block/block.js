/**
 * BLOCK: openstreetmap
 *
 */

import './editor.scss';
import './style.scss';
import edit from "./edit";
import save from "./save";
import deprecated from "./deprecated";

const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;

registerBlockType('ootb/openstreetmap', {
	title: __('Out of the Block: OpenStreetMap'),
	icon: 'location-alt',
	category: 'common',
	keywords: [
		__('Map block', 'ootb-openstreetmap'),
		__('Open Street Maps', 'ootb-openstreetmap'),
		__('Contact', 'ootb-openstreetmap'),
		__('Locations', 'ootb-openstreetmap'),
	],
	supports: {
		align: ['wide', 'full'],
	},
	attributes: {
		mapHeight: {
			type: 'integer',
			default: 400,
		},
		markers: {
			type: 'array',
			default: [],
		},
		zoom: {
			type: 'integer',
			default: 8,
		},
		minZoom: {
			type: 'integer',
			default: 2,
		},
		maxZoom: {
			type: 'integer',
			default: 18,
		},
		dragging: {
			type: 'boolean',
			default: true,
		},
		touchZoom: {
			type: 'boolean',
			default: true,
		},
		doubleClickZoom: {
			type: 'boolean',
			default: true,
		},
		scrollWheelZoom: {
			type: 'boolean',
			default: true,
		},
		defaultIcon: {
			type: 'object',
			default: null,
		},
		bounds: {
			type: 'array',
			default: [
				[37.97155174977503, 23.72656345367432]
			],
		},
	},
	edit,
	save,
	deprecated,
});
