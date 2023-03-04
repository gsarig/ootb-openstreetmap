import getFallbackIcon from "./getFallbackIcon";

export default function hasIconOverride(props, iconUrl, index) {
	const {
		attributes: {
			defaultIcon,
		},
	} = props;
	if (typeof index === 'undefined' && defaultIcon) {
		return true;
	}
	if (typeof index === 'undefined' || !iconUrl) {
		return false;
	}
	const defaultLinks = [
		defaultIcon?.icon,
		getFallbackIcon()
	];
	return !defaultLinks.includes(iconUrl);
}
