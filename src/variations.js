// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';

if (ootbGlobal?.options?.geodata) {
    wp.blocks.registerBlockVariation(
        'ootb/openstreetmap',
        {
            name: 'custom-fields',
            title: __('OpenStreetMap from custom fields', 'ootb-openstreetmap'),
            icon: 'location',
            attributes: {
                queryCustomFields: true,
                showMapData: false,
                showSearchBox: false,
                serverSideRender: true,
            },
        }
    );
}
