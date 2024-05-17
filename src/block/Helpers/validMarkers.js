import validateCoordinates from './validateCoordinates';

export default function validMarkers(data) {
	try {
		const jsonData = JSON.parse(data.toString());
		if (jsonData && typeof jsonData === 'object') {
			let clean = [];
			for (const location of jsonData) {
				// Skip if no lat or lng exist.
				if (!location.lat || !location.lng) {
					continue;
				}
				// Skip if either the latitude or the longitude is invalid.
				if (!validateCoordinates(location.lat, location.lng)) {
					continue;
				}
				// If no ID exists, create one.
				location.id = location.id ?? Date.now();
				// Make sure that we don't import unnecessary keys.
				const {lat, lng, text, id} = location;
				clean.push({lat, lng, text, id});
			}
			return clean;
		}
	} catch (e) {
		return false;
	}
	return false;
}
