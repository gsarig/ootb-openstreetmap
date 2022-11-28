// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {SelectControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';

export default function ProviderControl({props}) {
    const {
        attributes: {
            provider,
        },
        setAttributes,
    } = props;

    const {
        adminUrl,
        options: {
            api_mapbox,
        }
    } = ootbGlobal;

    const providers = [
        {
            label: __('OpenStreetMap', 'ootb-openstreetmap'),
            value: 'openstreetmap',
        },
        {
            label: __('Mapbox', 'ootb-openstreetmap'),
            value: 'mapbox',
        },
        {
            label: __('Stamen', 'ootb-openstreetmap'),
            value: 'stamen',
        },
    ];

    let providerHelp = '';

    if ('mapbox' === provider && !api_mapbox) {
        providerHelp = (
            <span>
                {
                    createInterpolateElement(
                        __('Make sure that you provided a valid API key on the <a>plugin settings</a>.', 'ootb-openstreetmap'),
                        {
                            a: <a href={adminUrl}/>,
                        }
                    )
                }
            </span>
        );
    } else if (!provider || 'openstreetmap' === provider) {
        // noinspection JSXNamespaceValidation
        providerHelp = (
            <span>
                {createInterpolateElement(
                    __('Heavy usage of OSM tiles is forbidden, and you might want to switch to a different tile provider. Read more on the <a>plugin settings</a>.', 'ootb-openstreetmap'),
                    {
                        a: <a href={adminUrl}/>,
                    }
                )}
            </span>
        );
    }

    return (
        <SelectControl
            label={__('Tile Layer Provider', 'ootb-openstreetmap')}
            value={provider}
            options={providers}
            onChange={(selectedProvider) => {
                setAttributes({
                    provider: selectedProvider,
                });
            }}
            help={providerHelp}
        />
    );
}
