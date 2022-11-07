import {__} from '@wordpress/i18n';

export default function Alert({props}) {
	const {
		attributes: {
			addingMarker,
		},
	} = props;

	let alert = '';
	if (' pinning' === addingMarker) {
		alert = __('Release to drop a marker here', 'ootb-openstreetmap');
	}

	// noinspection JSXNamespaceValidation
	return alert ?
		<div className="ootb-openstreetmap--alert">{alert}</div>
		: null;
}
