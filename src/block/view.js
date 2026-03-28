// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable
import createMapboxStyleUrl from "../common/createMapboxStyleUrl.js";

(function () {
	'use strict';

	const providers = ootb.providers;
	const options = ootb.options;
	const gestureHandlingOptions = ootb.gestureHandlingOptions;
	const maps = document.querySelectorAll('.ootb-openstreetmap--map');
	maps.forEach(renderMap);

	function renderMap(osmap) {
		try {
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
		const fullscreen = osmap.getAttribute('data-fullscreen');
		const enableClustering = osmap.getAttribute('data-enableclustering');
		const defaultIcon = JSON.parse(decodeURIComponent(escapedDefaultIcon));
		const locations = JSON.parse(decodeURIComponent(escapedMarkers));
		const mapType = osmap.getAttribute('data-maptype');
		const showMarkers = osmap.getAttribute('data-showmarkers');
		const escapedShapeStyle = osmap.getAttribute('data-shapestyle');
		const shapeStyle = JSON.parse(decodeURIComponent(escapedShapeStyle));
		const shapeText = osmap.getAttribute('data-shapetext');
		const mapboxstyleAttr = osmap.getAttribute('data-mapboxstyle');
		let mapboxstyle = '';
		if (mapboxstyleAttr) {
			mapboxstyle = mapboxstyleAttr;
		} else {
			const mapboxstyleGlobal = createMapboxStyleUrl(options.global_mapbox_style_url, options.api_mapbox);
			if (mapboxstyleGlobal) {
				mapboxstyle = encodeURIComponent(JSON.stringify(mapboxstyleGlobal));
			}
		}

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

		const clusterGroup = ('true' === enableClustering && typeof L.markerClusterGroup === 'function')
			? L.markerClusterGroup(ootb.clusterOptions ?? {})
			: null;

		const map = initializeMapView(osmap, mapOptions, zoom, locations, escapedDefaultIcon, clusterGroup);

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
		if ('true' === fullscreen && L.Control.Fullscreen) {
			map.addControl(new L.Control.Fullscreen());
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

			if (clusterGroup) {
				clusterGroup.addTo(map);
			}
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
			if (clusterGroup) {
				clusterGroup.addLayer(marker);
			} else {
				marker.addTo(map);
			}
		}
		} catch (e) {
			// eslint-disable-next-line no-console
			console.error('OOTB OpenStreetMap: failed to render map', e);
		}
	}

	function initializeMapView(osmap, mapOptions, zoom, locations, defaultIconString, clusterGroup) {
		const map = L.map(osmap, mapOptions);
		const boundsCheck = JSON.parse(osmap.getAttribute('data-bounds'));

		if (boundsCheck[0] !== null && boundsCheck[1] !== null) {
			map.setView(boundsCheck, parseInt(zoom));
		} else if (clusterGroup) {
			// When clustering is enabled, fit bounds from raw coordinates.
			// Markers will be added to the cluster group via renderLocation.
			const latLngs = locations.map(location => [location.lat, location.lng]);
			if (latLngs.length) {
				map.fitBounds(L.latLngBounds(latLngs));
			}
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
