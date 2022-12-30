// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';

const {BaseControl, Button} = wp.components;

export default function ExportControl({props}) {
	const {
		attributes: {
			markers,
		},
		clientId,
	} = props;
	const hasMarkers = markers && 0 < markers.length;
	const formBlob = new Blob([JSON.stringify(markers)], {type: 'application/json'});
	return (
		<BaseControl
			label={__('Export', 'ootb-openstreetmap')}
			help={__('Download a JSON file with the locations of this map.', 'ootb-openstreetmap')}
		>
			<div>
				<Button
					icon="download"
					iconPosition="left"
					variant={hasMarkers ? 'primary' : 'secondary'}
					href={window.URL.createObjectURL(formBlob)}
					target="_blank"
					download={'ootb_' + clientId}
					disabled={!hasMarkers}
					showTooltip={true}
					text={__('Export locations', 'ootb-openstreetmap')}
				/>
			</div>
		</BaseControl>
	);
}
