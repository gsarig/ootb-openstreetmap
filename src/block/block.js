/**
 * BLOCK: openstreetmap
 *
 */

import './editor.scss';
import './style.scss';
import edit from "./edit";
import save from "./save";
import deprecated from "./deprecated";
//noinspection JSUnresolvedVariable
const {__} = wp.i18n;
//noinspection JSUnresolvedVariable
const {registerBlockType} = wp.blocks;

registerBlockType('ootb/openstreetmap', {
	title: __('OpenStreetMap by Out of the Block'),
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
		provider: {
			type: 'string',
			default: 'openstreetmap',
		},
	},
	edit,
	save,
	deprecated,
});
