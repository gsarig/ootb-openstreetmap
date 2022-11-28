import {useMap} from "react-leaflet/hooks";

export default function MapUpdate({props}) {
	const {
		attributes: {
			mapHeight
		},
	} = props;
	const map = useMap();

	const mapContainer = map.getContainer();
	if (parseInt(mapContainer.style.height) !== mapHeight) {
		mapContainer.style.height = `${mapHeight}px`;
	}

	return null;
}
