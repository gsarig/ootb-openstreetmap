import centerMap from './Helpers/centerMap';
import getIcon from './Helpers/getIcon';
import createMapboxStyleUrl from '../common/createMapboxStyleUrl';
import { useBlockProps } from '@wordpress/block-editor';

export default function save(props, className) {
	const {
		attributes: {
			mapHeight,
			markers,
			zoom,
			minZoom,
			maxZoom,
			dragging,
			touchZoom,
			doubleClickZoom,
			scrollWheelZoom,
			fullscreen,
			provider,
			mapType,
			showMarkers,
			shapeColor,
			shapeWeight,
			shapeText,
			mapboxStyleUrl,
		},
	} = props;
	const shapeStyles = {
		fillColor: shapeColor,
		color: shapeColor,
		weight: shapeWeight
	}
	const mapboxApi = ootbGlobal?.options?.api_mapbox ?? null;
	const mapboxStyle = mapboxStyleUrl && mapboxApi ?
		createMapboxStyleUrl(mapboxStyleUrl, mapboxApi) :
		'';
	return markers ? (
		<div {...useBlockProps.save({className})}>
			<div className="ootb-openstreetmap--map"
				 data-provider={provider}
				 data-maptype={mapType}
				 data-showmarkers={showMarkers}
				 data-shapestyle={encodeURIComponent(JSON.stringify(shapeStyles))}
				 data-shapetext={shapeText}
				 data-markers={encodeURIComponent(JSON.stringify(markers))} // Escape because of the potential HTML in the output.
				 data-bounds={JSON.stringify(centerMap(props))}
				 data-zoom={zoom}
				 data-minzoom={minZoom}
				 data-maxzoom={maxZoom}
				 data-dragging={dragging}
				 data-touchzoom={touchZoom}
				 data-doubleclickzoom={doubleClickZoom}
				 data-scrollwheelzoom={scrollWheelZoom}
				 data-fullscreen={fullscreen}
				 data-marker={encodeURIComponent(JSON.stringify(getIcon(props)))}
				 data-mapboxstyle={mapboxStyle ? encodeURIComponent(JSON.stringify(mapboxStyle)) : null}
				 style={
					 {
						 height: mapHeight + 'px'
					 }
				 }
			>
			</div>
		</div>
	) : null;
}
