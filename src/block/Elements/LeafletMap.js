import centerMap from '../Helpers/centerMap';
import TileProvider from './TileProvider';
import Markers from './Markers';
import Shapes from './Shapes';
import {MapContainer} from 'react-leaflet';
import MapEvents from './MapEvents';
import MapUpdate from './MapUpdate';

export default function LeafletMap({addingMarker, setAddingMarker, ...props}) {
	const {
		attributes: {
			zoom,
			bounds,
			mapHeight,
			mapType,
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
			<MapEvents props={props} addingMarker={addingMarker} setAddingMarker={setAddingMarker}/>
			<MapUpdate props={props}/>
			<TileProvider props={props}/>
			<Markers props={props} setAddingMarker={setAddingMarker}/>
			{'marker' !== mapType ?
				<Shapes props={props}/>
				: null}
		</MapContainer>
	);
}
