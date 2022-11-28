import fitBounds from './fitBounds';

export default function getBounds(props, newMarker = [], mapObject = null) {
	const {
		attributes: {
			markers,
			zoom,
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
	if (mapObject) {
		const lastZoom = mapObject.getZoom();
		if (zoom !== lastZoom) {
			setAttributes({zoom: lastZoom});
		}
	}
}
