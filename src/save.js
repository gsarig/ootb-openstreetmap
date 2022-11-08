import centerMap from "./Helpers/centerMap";
import getIcon from "./Helpers/getIcon";

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
			provider,
		},
	} = props;

	return markers ? (
		<div className={className}>
			<div className="ootb-openstreetmap--map"
				 data-provider={provider}
				 data-markers={escape(JSON.stringify(markers))} // Escape because of the potential HTML in the output.
				 data-bounds={JSON.stringify(centerMap(props))}
				 data-zoom={zoom}
				 data-minzoom={minZoom}
				 data-maxzoom={maxZoom}
				 data-dragging={dragging}
				 data-touchzoom={touchZoom}
				 data-doubleclickzoom={doubleClickZoom}
				 data-scrollwheelzoom={scrollWheelZoom}
				 data-marker={escape(JSON.stringify(getIcon(props)))}
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
