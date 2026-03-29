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
	const centerFromBounds = getBoundsCenter(bounds);
	if (centerFromBounds) {
		return centerFromBounds;
	}
	if (markers.length) {
		return [markers[0].lat, markers[0].lng];
	}
	//noinspection JSUnresolvedVariable
	return getBoundsCenter(ootbGlobal.defaultLocation) || [0, 0];
}
