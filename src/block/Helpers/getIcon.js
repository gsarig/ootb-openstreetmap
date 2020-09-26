export default function getIcon(props) {
	const {
		attributes: {
			defaultIcon,
		},
	} = props;

	//noinspection JSUnresolvedVariable
	const {
		pluginDirUrl,
	} = ootbGlobal;
	const fallbackIcon = pluginDirUrl + 'assets/vendor/leaflet/images/marker-icon.png';
	const horizontalPosition = defaultIcon ? Math.round(defaultIcon.width / 2) : 12;
	const verticalPosition = defaultIcon ? Math.round(defaultIcon.height) : 41;
	return {
		iconUrl: defaultIcon ? defaultIcon.url : fallbackIcon,
		iconAnchor: [horizontalPosition, verticalPosition],
		popupAnchor: [0, -Math.abs(verticalPosition)],
	}
}
