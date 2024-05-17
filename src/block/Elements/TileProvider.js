import {TileLayer} from 'react-leaflet';
// noinspection NpmUsedModulesInstalled
import {useEffect, useRef} from '@wordpress/element';
import createMapboxStyleUrl from '../../../assets/shared/createMapboxStyleUrl';

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
	const ref = useRef(null);
	useEffect(() => {
		ref.current.setUrl(providerUrl);
	}, [providerUrl]);
	return (
		<TileLayer
			ref={ref}
			url={providerUrl}
			attribution={providerAttribution}
		/>
	);
}
