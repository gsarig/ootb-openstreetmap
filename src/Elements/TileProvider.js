import {TileLayer} from 'react-leaflet';
// noinspection NpmUsedModulesInstalled
import {useEffect, useRef} from '@wordpress/element';

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
