// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

(function () {
	'use strict';

	const providers = ootb.providers;
	const options = ootb.options;
	const gestureHandlingOptions = ootb.gestureHandlingOptions;
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
		const defaultIcon = JSON.parse(decodeURIComponent(escapedDefaultIcon));
		const locations = JSON.parse(decodeURIComponent(escapedMarkers));
		const mapType = osmap.getAttribute('data-maptype');
		const showMarkers = osmap.getAttribute('data-showmarkers');
		const escapedShapeStyle = osmap.getAttribute('data-shapestyle');
		const shapeStyle = JSON.parse(decodeURIComponent(escapedShapeStyle));
		const shapeText = osmap.getAttribute('data-shapetext');
		const mapboxstyle = osmap.getAttribute('data-mapboxstyle');

		let apiKey = '';
		if ('mapbox' === provider) {
			apiKey = options.api_mapbox;
		}

		let providerUrl = providers[provider].url;
		if ('mapbox' === provider && mapboxstyle) {
			providerUrl = JSON.parse(decodeURIComponent(mapboxstyle));
		} else {
			providerUrl += apiKey;
		}

		const mapOptions = {
			minZoom: parseInt(minZoom),
			maxZoom: parseInt(maxZoom),
		};

		if (options.prevent_default_gestures) {
			mapOptions.gestureHandling = true;
			if (gestureHandlingOptions && Object.keys(gestureHandlingOptions).length > 0) {
				mapOptions.gestureHandlingOptions = gestureHandlingOptions;
			}
		}

		const map = initializeMapView(osmap, mapOptions, zoom, locations, escapedDefaultIcon);

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

		L.tileLayer(providerUrl, {
			attribution: providers[provider].attribution
		}).addTo(map);

		if (!locations || !locations.length) return; // If there are no locations, don't go any further

		if ('polygon' === mapType) {
			const polygon = L.polygon(locations, shapeStyle).addTo(map);
			map.fitBounds(polygon.getBounds());
			if (shapeText.length) {
				polygon.bindPopup(shapeText);
			}
		} else if ('polyline' === mapType) {
			const polyline = L.polyline(locations, shapeStyle).addTo(map);
			map.fitBounds(polyline.getBounds());
			if (shapeText.length) {
				polyline.bindPopup(shapeText);
			}
		}

		if ('false' !== showMarkers) {
			// Render the locations
			locations.forEach(renderLocation);
		}

		// Render a location's marker
		function renderLocation(location) {
			const markerIcon = structuredClone(defaultIcon);
			if (location.icon) {
				markerIcon.iconUrl = location.icon.url;
			}
			let marker = L.marker([location.lat, location.lng], {
				icon: L.icon(markerIcon),
			});
			if (location.text) {
				marker.bindPopup(location.text);
			}
			marker.addTo(map);
		}
	}

	function initializeMapView(osmap, mapOptions, zoom, locations, defaultIconString) {
		const map = L.map(osmap, mapOptions);
		const boundsCheck = JSON.parse(osmap.getAttribute('data-bounds'));

		if (boundsCheck[0] !== null && boundsCheck[1] !== null) {
			map.setView(boundsCheck, parseInt(zoom));
		} else {
			let markers = [];
			locations.forEach(location => {
				const defaultIcon = JSON.parse(decodeURIComponent(defaultIconString));
				const markerIcon = structuredClone(defaultIcon);
				if (location.icon) {
					markerIcon.iconUrl = location.icon.url;
				}
				let marker = L.marker([location.lat, location.lng], {icon: L.icon(markerIcon)});
				if (location.text) {
					marker.bindPopup(location.text);
				}
				markers.push(marker);
				marker.addTo(map);
			});

			// Use markers to calculate bounds.
			if (markers.length) {
				// Create a new feature group.
				let group = new L.featureGroup(markers);
				// Adjust the map to show all markers.
				map.fitBounds(group.getBounds());
			}
		}
		return map;
	}
})();
