import fitBounds from './fitBounds';

export default function getBounds(props, newMarker = [], mapObject = null) {
	const {
		attributes: {
			markers,
		},
		setAttributes,
	} = props;

	let boundsArr = [];
	if (newMarker && 'undefined' !== typeof newMarker.lat) {
		boundsArr.push([newMarker.lat, newMarker.lng]);
	}
	const markersArr = Object.entries(markers);
	//noinspection JSUnusedLocalSymbols
	markersArr.forEach(([index, value]) => {
		if (value) {
			boundsArr.push([value.lat, value.lng]);
		}
	});
	if (boundsArr.length) {
		setAttributes({bounds: boundsArr});
		fitBounds(boundsArr, mapObject);
	}
}
