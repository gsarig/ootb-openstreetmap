import getFallbackIcon from '../../common/getFallbackIcon';

export default function getIcon(props, index) {
	const {
		attributes: {
			markers,
			defaultIcon,
		},
	} = props;
	//noinspection JSUnresolvedVariable
	const {
		pluginDirUrl,
	} = ootbGlobal;

	const currentIcon = () => {
		if (typeof index !== 'undefined' && markers[index]?.icon) {
			return markers[index].icon;
		}
		return defaultIcon ?? null;
	}
	const horizontalPosition = currentIcon() ? Math.round(currentIcon().width / 2) : 12;
	const verticalPosition = currentIcon() ? Math.round(currentIcon().height) : 41;
	return {
		iconUrl: currentIcon() ? currentIcon().url : getFallbackIcon(),
		iconAnchor: [horizontalPosition, verticalPosition],
		popupAnchor: [0, -Math.abs(verticalPosition)],
	}
}
