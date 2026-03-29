export default function getMarkerIndex(e, markers) {
	const markerId = e.target.options.markerId;
	const index = markers.findIndex(m => m.id === markerId);
	return index !== -1 ? index : null;
}
