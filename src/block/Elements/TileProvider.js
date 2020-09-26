import {TileLayer} from "react-leaflet";

export default function TileProvider({props}) {
	const {
		attributes: {
			provider,
		},
	} = props;

	const mapBoxKey = ootbGlobal.options.api_mapbox;
	const providers = ootbGlobal.providers;

	let providerUrl = providers.openstreetmap.url;
	let providerAttribution = providers.openstreetmap.attribution;

	if ('mapbox' === provider && mapBoxKey) {
		providerUrl = providers.mapbox.url + mapBoxKey;
		providerAttribution = providers.mapbox.attribution;
	}

	// noinspection JSXNamespaceValidation
	return (
		<TileLayer
			url={providerUrl}
			attribution={providerAttribution}
		/>
	);
}
