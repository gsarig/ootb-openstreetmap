import centerMap from '../Helpers/centerMap';
import TileProvider from './TileProvider';
import Markers from './Markers';
import {MapContainer} from 'react-leaflet';
import MapEvents from './MapEvents';
import MapUpdate from './MapUpdate';

export default function LeafletMap({props}) {
	const {
		attributes: {
			zoom,
			bounds,
			mapHeight,
		},
	} = props;

	return (
		<MapContainer
			center={centerMap(props)}
			zoom={zoom}
			bounds={bounds}
			style={
				{
					height: mapHeight + 'px'
				}
			}
		>
			<MapEvents props={props}/>
			<MapUpdate props={props}/>
			<TileProvider props={props}/>
			<Markers props={props}/>
		</MapContainer>
	);
}
