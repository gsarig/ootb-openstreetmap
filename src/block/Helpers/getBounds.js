export default function getBounds(props) {
	const {
		attributes: {
			markers
		},
		setAttributes,
	} = props;
	let boundsArr = [];
	const markersArr = Object.entries(markers);
	markersArr.forEach(([key, value]) => {
		boundsArr.push([value.lat, value.lng]);
	});
	setAttributes({bounds: boundsArr});
}
