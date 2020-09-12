import getBoundsCenter from "./getBoundsCenter";

export default function centerMap(props) {
	const {
		attributes: {
			bounds,
			markers,
		},
	} = props;
	return getBoundsCenter(bounds) || [markers[0].lat, markers[0].lng];
}
