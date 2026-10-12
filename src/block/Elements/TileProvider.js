import createMapboxStyleUrl from '../../common/createMapboxStyleUrl';
import FetchTileLayer from './FetchTileLayer';

export default function TileProvider({props}) {
	const {
		attributes: {
			provider,
			mapboxStyleUrl,
		},
	} = props;

	//noinspection JSUnresolvedVariable
	const {
		options: {
			api_mapbox,
			global_mapbox_style_url,
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
		providerUrl = mapboxStyleUrl || global_mapbox_style_url ?
			createMapboxStyleUrl(mapboxStyleUrl.length ? mapboxStyleUrl : global_mapbox_style_url, api_mapbox) :
			mapbox.url + api_mapbox;
		providerAttribution = mapbox.attribution;
	}
	if ('stamen' === provider) {
		providerUrl = stamen.url;
		providerAttribution = stamen.attribution;
	}
	return (
		<FetchTileLayer
			url={providerUrl}
			attribution={providerAttribution}
		/>
	);
}
