import { useEffect, useState } from '@wordpress/element';
import { useMap } from 'react-leaflet/hooks';
import L from 'leaflet';

/**
 * Tile layer for the editor that loads each tile with fetch() instead of <img src>.
 *
 * From WordPress 7.1 the editor canvas is always an iframe loaded from a blob: URL
 * (7.0 only iframes it in some modes, such as device preview), and browsers send no
 * Referer for requests made from a blob: document, whatever the referrer policy.
 * tile.openstreetmap.org answers Referer-less tile requests with its "Access
 * blocked" image, so plain <img> tiles break in the editor. This script runs in
 * the parent wp-admin window, so fetch() sends that page's origin as Referer
 * even though the tile <img> lives inside the iframe.
 *
 * Do not replace this with react-leaflet's TileLayer or a referrerPolicy option:
 * neither can attach a Referer to a request made from a blob: document.
 */
const FetchingTileLayer = L.TileLayer.extend({
	createTile(coords, done) {
		const tile = document.createElement('img');
		const url = this.getTileUrl(coords);

		L.DomEvent.on(tile, 'load', () => done(null, tile));
		L.DomEvent.on(tile, 'error', (e) => done(e, tile));
		tile.alt = '';
		tile.setAttribute('role', 'presentation');

		fetch(url, { mode: 'cors', credentials: 'omit' }).then(
			(response) => {
				// The provider answered: report HTTP errors (404 past the map edge, 429 rate
				// limiting) as tile errors rather than repeating the request as an image.
				if (!response.ok) {
					done(new Error(`Tile request failed with status ${response.status}`), tile);
					return;
				}
				response
					.blob()
					.then((blob) => {
						const objectUrl = URL.createObjectURL(blob);
						const revoke = () => URL.revokeObjectURL(objectUrl);
						tile.addEventListener('load', revoke, { once: true });
						tile.addEventListener('error', revoke, { once: true });
						tile.src = objectUrl;
					})
					.catch((e) => done(e, tile));
			},
			// fetch() itself failed: a network error, or a provider without CORS headers.
			// Fall back to a plain image. The browser has already sent the request once,
			// so providers without CORS headers cost two requests per tile.
			() => {
				tile.src = url;
			}
		);

		return tile;
	},
});

export default function FetchTileLayer({ url, attribution }) {
	const map = useMap();
	const [layer] = useState(() => new FetchingTileLayer(url, { attribution }));

	useEffect(() => {
		layer.addTo(map);
		return () => {
			layer.remove();
		};
	}, [map, layer]);

	useEffect(() => {
		layer.setUrl(url);
	}, [layer, url]);

	return null;
}
