import {useMap} from "react-leaflet/hooks";

export default function MapUpdate({props}) {
	const {
		attributes: {
			zoom,
			bounds,
			mapHeight
		},
	} = props;
	const map = useMap();
	if (zoom !== map.getZoom()) {
		map.setZoom(zoom);
	}
	// map.fitBounds(bounds);
	const mapContainer = map.getContainer();
	if (parseInt(mapContainer.style.height) !== mapHeight) {
		mapContainer.style.height = `${mapHeight}px`;
	}

	return null;
}
