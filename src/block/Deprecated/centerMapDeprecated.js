import getBoundsCenter from '../../common/getBoundsCenter';

export default function centerMapDeprecated(props) {
	const {
		attributes: {
			bounds,
			markers,
		},
	} = props;
	return getBoundsCenter(bounds) || [markers[0].lat, markers[0].lng];
}
