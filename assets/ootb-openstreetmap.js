(function () {
	'use strict';

	const maps = document.querySelectorAll('.ootb-openstreetmap--map');
	maps.forEach(renderMap);

	function renderMap(osmap) {
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

		if (locations && locations.length) {
			const map = L.map(osmap, {
				minZoom: parseInt(minZoom),
				maxZoom: parseInt(maxZoom),
			}).setView(JSON.parse(bounds), parseInt(zoom));

			// Render the locations
			locations.forEach(renderLocation);

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

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(map);

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

	}

})();
