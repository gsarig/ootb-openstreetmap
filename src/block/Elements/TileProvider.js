import {TileLayer} from "react-leaflet";

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
		}
	} = ootbGlobal;

	let providerUrl = openstreetmap.url;
	let providerAttribution = openstreetmap.attribution;

	if ('mapbox' === provider && api_mapbox) {
		providerUrl = mapbox.url + api_mapbox;
		providerAttribution = mapbox.attribution;
	}

	// noinspection JSXNamespaceValidation
	return (
		<TileLayer
			url={providerUrl}
			attribution={providerAttribution}
		/>
	);
}
