import fitBounds from "./fitBounds";

export default function getBounds(props, newMarker = [], mapObj = null) {
	const {
		attributes: {
			markers,
		},
		setAttributes,
	} = props;
	if (markers && markers.length > 0) {
		let boundsArr = [];
		if (newMarker && 'undefined' !== typeof newMarker.lat) {
			boundsArr.push([newMarker.lat, newMarker.lng]);
		}
		const markersArr = Object.entries(markers);
		markersArr.forEach(([index, value]) => {
			if (value) {
				boundsArr.push([value.lat, value.lng]);
			}
		});
		if (boundsArr.length > 1) {
			setAttributes({bounds: boundsArr});
			fitBounds(boundsArr, mapObj)
		}
	}
}
