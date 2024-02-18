// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {SelectControl, TextControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';

export default function ProviderControl({props}) {
	const {
		attributes: {
			provider,
			mapboxStyleUrl,
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

	const mapboxStyleUrlField = ('mapbox' === provider) ?
		(
			<TextControl
				label={__('Mapbox Style URL', 'ootb-openstreetmap')}
				value={mapboxStyleUrl}
				onChange={(value) => {
					setAttributes({
						mapboxStyleUrl: value,
					});
				}}
				help={(
					<span>
						{createInterpolateElement(
							__('You can find the style URL in the <mapbox_url>Mapbox Studio</mapbox_url>. There, use the "Share" button, and under "Developer resources", copy the "Style URL". It should look like that: <code>mapbox://styles/username/style-id</code>. For the styles to work, you need to have a Mapbox API key set in the <a>plugin settings</a>.', 'ootb-openstreetmap'),
							{
								mapbox_url: <a href="https://studio.mapbox.com/"/>,
								code: <code/>,
								a: <a href={adminUrl}/>
							}
						)}
					</span>
				)}
			/>
		) : null;

	return (
		<>
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
			{mapboxStyleUrlField}
		</>
	);
}
