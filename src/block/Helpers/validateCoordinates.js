/**
 * Perform basic validation on the coordinates.
 * @param lat
 * @param lng
 * @returns {*}
 */
export default function validateCoordinates(lat = '', lng = '') {
	if (!lat || !lng) {
		return false;
	}
	return isValidLatitude(parseFloat(lat)) && isValidLongitude(parseFloat(lng));
}

export function isValidLatitude(lat) {
	return isFinite(lat) && Math.abs(lat) <= 90;
}

export function isValidLongitude(lng) {
	return isFinite(lng) && Math.abs(lng) <= 180;
}
