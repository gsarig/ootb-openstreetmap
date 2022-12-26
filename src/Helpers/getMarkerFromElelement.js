import L from "leaflet";

export default function getMarkerFromElelement(props, e) {
	const {
		attributes: {
			markers,
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
	/**
	 * To be able to identify the markers, we need to assign them a consistent, unique ID.
	 * `L.Util.stamp()` allows us to get the marker's ID, but there seems to be an inconsistency in what `L.Util.stamp()` returns here, when the marker gets dropped, compared to what it will return when we click on an already dropped marker.
	 * Moreover, the logic of the ID seems to change depending on whether the marker comes from a standard drop of from a search.
	 */
	const markersNum = markers.length;
	/**
	 * On standard drops, the marker's ID is the Leaflet stamp + the number of the markers + 1 (don't ask why, I have no idea).
	 */
	let markerId = L.Util.stamp(e) + (markersNum + 1);
	/**
	 * If the marker comes from the search, then we need to account for a few specific edge cases.
	 */
	if (isSearching) {
		/**
		 * If we are on the first search AND if there are no other markers added so far, then the marker's ID is the Leaflet stamp + 13 (again, I have no idea why it works).
		 */
		if (0 === searchCount && 0 === markersNum) {
			markerId = L.Util.stamp(e) + 13;
			/**
			 * Finally, we have two more edge cases, where the ID is the Leaflet stamp + the markers number + 19:
			 * - If we are on the second search and there is only one other marker so far.
			 * - If we are on the first search, but there are already other markers on the map.
			 */
		} else if ((1 === searchCount && 1 === markersNum) || (0 === searchCount && 0 < markersNum)) {
			markerId = L.Util.stamp(e) + (markersNum + 19);
		}
	}
	return {
		lat: markerLat.toString(),
		lng: markerLng.toString(),
		text: markerText,
		id: markerId,
	}
}
