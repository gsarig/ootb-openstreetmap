// noinspection JSUnresolvedVariable
const {__} = wp.i18n;
// noinspection JSUnresolvedVariable
const {SelectControl} = wp.components;

export default function ProviderControl({props}) {
	const {
		attributes: {
			provider,
		},
		setAttributes,
	} = props;
	const providers = [
		{
			label: __('OpenStreetMap', 'ootb-openstreetmap'),
			value: 'openstreetmap',
		},
		{
			label: __('Mapbox', 'ootb-openstreetmap'),
			value: 'mapbox',
		},
	];

	const mapBoxKey = ootbGlobal.options.api_mapbox;
	let providerHelp = '';

	if ('mapbox' === provider && !mapBoxKey) {
		providerHelp = (
			<p>
				{__('Make sure that you provided a valid API key on the ', 'ootb-openstreetmap')}
				<a href={ootbGlobal.adminUrl}>{__('plugin settings', 'ootb-openstreetmap')}</a>.
			</p>
		);
	} else if ('openstreetmap' === provider) {
		providerHelp = (
			<p>
				{__('Heavy usage of OSM tiles is forbidden and you might want to switch to a different tile provider. Read more on the ', 'ootb-openstreetmap')}
				<a href={ootbGlobal.adminUrl}>{__('plugin settings', 'ootb-openstreetmap')}</a>.
			</p>
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
