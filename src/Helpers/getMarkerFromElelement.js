export default function getMarkerFromElelement({searchCount, setSearchCount, searchResults}, e) {
	const isSearching = ('undefined' !== typeof searchResults && 0 < searchResults.length);
	let markerLat;
	let markerLng;
	let markerText;
	let markerTextRaw;
	if (isSearching) {
		const updatedSearchCount = searchCount + 1;
		setSearchCount(updatedSearchCount);
		const index = e.target.getAttribute('data-index');
		const {
			lat,
			lon,
			display_name
		} = searchResults[index];
		markerLat = lat ?? '';
		markerLng = lon ?? '';
		markerText = display_name ? `<p>${display_name}</p>` : '';
		markerTextRaw = display_name ?? '';
	} else {
		markerLat = e.latlng.lat ?? '';
		markerLng = e.latlng.lng ?? '';
		markerText = '';
		markerTextRaw = '';
	}

	if (!markerLat || !markerLng) {
		return;
	}

	return {
		lat: markerLat.toString(),
		lng: markerLng.toString(),
		text: markerText,
		textRaw: markerTextRaw,
		id: Date.now(),
	}
}
