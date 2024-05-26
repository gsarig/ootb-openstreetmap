export default function getIconDeprecated(props) {
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
	const horizontalPosition = defaultIcon ? defaultIcon.width / 2 : 12;
	return {
		iconUrl: defaultIcon ? defaultIcon.url : fallbackIcon,
		iconAnchor: [horizontalPosition, 0],
	}
}
