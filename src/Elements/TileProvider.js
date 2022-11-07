import {TileLayer} from 'react-leaflet';

export default function TileProvider({props}) {
	const {
		attributes: {
			provider,
		},
	} = props;

	//noinspection JSUnresolvedVariable
	const {
		options: {
			api_mapbox,
		},
		providers: {
			openstreetmap,
			mapbox,
			stamen,
		}
	} = ootbGlobal;

	let providerUrl = openstreetmap.url;
	let providerAttribution = openstreetmap.attribution;

	if ('mapbox' === provider && api_mapbox) {
		providerUrl = mapbox.url + api_mapbox;
		providerAttribution = mapbox.attribution;
	}
	if ('stamen' === provider) {
		providerUrl = stamen.url;
		providerAttribution = stamen.attribution;
	}

	// noinspection JSXNamespaceValidation
	return (
		<TileLayer
			url={providerUrl}
			attribution={providerAttribution}
		/>
	);
}
