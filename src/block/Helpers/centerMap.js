import getBoundsCenter from '../../common/getBoundsCenter';

export default function centerMap(props) {
	const {
		attributes: {
			bounds,
			showDefaultBounds,
			markers,
		},
	} = props;
	if (true === showDefaultBounds) {
		//noinspection JSUnresolvedVariable
		return getBoundsCenter(ootbGlobal.defaultLocation);
	}
	return getBoundsCenter(bounds) || [markers[0]?.lat, markers[0]?.lng];
}
