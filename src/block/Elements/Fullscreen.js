import { useEffect } from '@wordpress/element';
import { useMap } from 'react-leaflet/hooks';
import { __ } from '@wordpress/i18n';
import L from 'leaflet';
import '../../../assets/vendor/leaflet-fullscreen/leaflet.fullscreen.css';

export default function Fullscreen({ fullscreen }) {
	const map = useMap();

	useEffect(() => {
		if (!map) return;

		let fullscreenControl = null;

		// Check if the plugin is loaded and the attribute is true
		if (fullscreen && L.Control.Fullscreen) {
			fullscreenControl = new L.Control.Fullscreen({
				title: {
					'false': __( 'View Fullscreen', 'ootb-openstreetmap' ),
					'true': __( 'Exit Fullscreen', 'ootb-openstreetmap' ),
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
