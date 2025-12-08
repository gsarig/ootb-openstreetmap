import centerMap from '../Helpers/centerMap';
import TileProvider from './TileProvider';
import Markers from './Markers';
import Shapes from './Shapes';
import {MapContainer} from 'react-leaflet';
import MapEvents from './MapEvents';
import MapUpdate from './MapUpdate';
import Fullscreen from './Fullscreen';

export default function LeafletMap({inBlockEditor, addingMarker, setAddingMarker, ...props}) {
	const {
		attributes: {
			zoom,
			bounds,
			mapHeight,
			mapType,
			fullscreen,
		},
	} = props;

	return (
		<MapContainer
			center={centerMap(props)}
			zoom={zoom}
			dragging={inBlockEditor} // Dragging doesn't work well when the block is used in a template part. Disabling it as a temporary workaround.
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
			<Fullscreen fullscreen={fullscreen}/>
			<Markers props={props} setAddingMarker={setAddingMarker}/>
			{'marker' !== mapType ?
				<Shapes props={props}/>
				: null}
		</MapContainer>
	);
}
