(function () {
	'use strict';

	const providers = ootb.providers;
	const options = ootb.options;
	const maps = document.querySelectorAll('.ootb-openstreetmap--map');
	maps.forEach(renderMap);

	function renderMap(osmap) {
		const provider = osmap.getAttribute('data-provider') || 'openstreetmap';
		const escapedMarkers = osmap.getAttribute('data-markers');
		const escapedDefaultIcon = osmap.getAttribute('data-marker');
		const zoom = osmap.getAttribute('data-zoom');
		const minZoom = osmap.getAttribute('data-minzoom');
		const maxZoom = osmap.getAttribute('data-maxzoom');
		const dragging = osmap.getAttribute('data-dragging');
		const touchZoom = osmap.getAttribute('data-touchzoom');
		const doubleClickZoom = osmap.getAttribute('data-doubleclickzoom');
		const scrollWheelZoom = osmap.getAttribute('data-scrollwheelzoom');
		const bounds = osmap.getAttribute('data-bounds');
		const defaultIcon = JSON.parse(unescape(escapedDefaultIcon));
		const locations = JSON.parse(unescape(escapedMarkers));

		let apiKey = '';
		if ('mapbox' === provider) {
			apiKey = options.api_mapbox;
		}

		const map = L.map(osmap, {
			minZoom: parseInt(minZoom),
			maxZoom: parseInt(maxZoom),
		}).setView(JSON.parse(bounds), parseInt(zoom));

		// Set the rest of the map options
		if ('false' === dragging) {
			map.dragging.disable();
		}
		if ('false' === touchZoom) {
			map.touchZoom.disable();
		}
		if ('false' === doubleClickZoom) {
			map.doubleClickZoom.disable();
		}
		if ('false' === scrollWheelZoom) {
			map.scrollWheelZoom.disable();
		}

		L.tileLayer(providers[provider].url + apiKey, {
			attribution: providers[provider].attribution
		}).addTo(map);

		if (!locations || !locations.length) return; // If there are no locations, don't go any further

		// Render the locations
		locations.forEach(renderLocation);

		// Render a location's marker
		function renderLocation(location) {
			let marker = L.marker([location.lat, location.lng], {
				icon: L.icon(defaultIcon),
			});
			if (location.text) {
				marker.bindPopup(location.text);
			}
			marker.addTo(map);
		}
	}
})();
