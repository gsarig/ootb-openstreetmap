export default function getMarkerIndex(e, markers) {
	const markerId = e.target.options.markerId;
	let index = null;
	for (let key in markers) {
		if (markerId === markers[key].id) {
			index = key;
		}
	}
	return index;
}
