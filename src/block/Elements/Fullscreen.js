import { useEffect } from '@wordpress/element';
import { useMap } from 'react-leaflet/hooks';
import L from 'leaflet';
import '../../../assets/vendor/leaflet-fullscreen/leaflet.fullscreen.css';
import '../../../assets/vendor/leaflet-fullscreen/Leaflet.fullscreen.js';

export default function Fullscreen({ fullscreen }) {
	const map = useMap();

	useEffect(() => {
		if (!map) return;

		let fullscreenControl = null;

		// Check if the plugin is loaded and the attribute is true
		if (fullscreen && L.Control.Fullscreen) {
			fullscreenControl = new L.Control.Fullscreen({
				title: {
					'false': 'View Fullscreen',
					'true': 'Exit Fullscreen'
				}
			});
			map.addControl(fullscreenControl);
		}

		// Cleanup: Remove control when component unmounts or fullscreen toggles off
		return () => {
			if (fullscreenControl) {
				map.removeControl(fullscreenControl);
			}
		};
	}, [map, fullscreen]);

	return null;
}