/**
 * Converts Mapbox style URL into a format that Leaflet can use for requesting tiles.
 * @param {string} styleUrl - The Mapbox style URL.
 * @param {string} accessToken - The Mapbox access token.
 * @returns {string} The Leaflet URL.
 */
export default function createMapboxStyleUrl(styleUrl, accessToken) {
	if (!styleUrl || !accessToken ||
		typeof styleUrl !== 'string' ||
		typeof accessToken !== 'string' ||
		!styleUrl.startsWith('mapbox://')) {
		return '';
	}
	// Parse username and style ID from Mapbox URL
	const mapboxUrlParts = styleUrl.split('/');
	const username = mapboxUrlParts[3];
	const styleId = mapboxUrlParts[4];

	if (!username || !styleId ||
		typeof username !== 'string' ||
		typeof styleId !== 'string') {
		return '';
	}

	// Build Leaflet URL
	return `https://api.mapbox.com/styles/v1/${username}/${styleId}/tiles/{z}/{x}/{y}?access_token=${accessToken}`;
}
