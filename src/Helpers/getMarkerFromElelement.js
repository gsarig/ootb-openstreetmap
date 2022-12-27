export default function getMarkerFromElelement(props, e) {
	const {
		attributes: {
			searchResults,
			searchCount
		},
		setAttributes
	} = props;

	const isSearching = ('undefined' !== typeof searchResults && 0 < searchResults.length);
	let markerLat;
	let markerLng;
	let markerText;
	if (isSearching) {
		setAttributes({searchCount: searchCount + 1});
		const index = e.target.getAttribute('data-index');
		const {
			lat,
			lon,
			display_name
		} = searchResults[index];
		markerLat = lat ?? '';
		markerLng = lon ?? '';
		markerText = display_name ? `<p>${display_name}</p>` : '';
	} else {
		markerLat = e.latlng.lat ?? '';
		markerLng = e.latlng.lng ?? '';
		markerText = '';
	}

	if (!markerLat || !markerLng) {
		return;
	}

	return {
		lat: markerLat.toString(),
		lng: markerLng.toString(),
		text: markerText,
		id: Date.now(),
	}
}
