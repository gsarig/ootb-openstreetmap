// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';

wp.blocks.registerBlockVariation(
    'ootb/openstreetmap',
    {
        name: 'custom-fields',
        title: __('Map from Custom Fields', 'ootb-openstreetmap'),
        icon: 'location',
        attributes: {
            queryCustomFields: true,
            showMapData: false,
            showSearchBox: false,
        },
    }
);
