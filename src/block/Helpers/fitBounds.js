export default function fitBounds(bounds, mapObj = null) {
	if (mapObj && bounds) {
		mapObj.fitBounds(bounds, {padding: [50, 50]});
	}
}
