// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import validMarkers from "../Helpers/validMarkers";
import {BaseControl, FormFileUpload} from '@wordpress/components';

export default function ImportControl({props}) {
	const {
		attributes: {
			markers,
		},
		setAttributes,
	} = props;
	const hasMarkers = markers && 0 < markers.length;

	const importMarkers = (event) => {
		const file = event.currentTarget.files[0];
		if (!file || 0 === file.length) {
			return;
		}
		const reader = new FileReader();
		reader.readAsText(file, "UTF-8");
		reader.onload = function (fileObj) {
			const data = validMarkers(fileObj.target.result);
			if (!data) {
				return;
			}
			setAttributes({
				markers: markers.concat(data),
				shouldUpdateBounds: true,
			});
		}
		//noinspection JSUnusedLocalSymbols
		reader.onerror = function (fileObj) {
			console.log(__('Error reading file', 'ootb-openstreetmap'));
		}
	};

	return (
		<BaseControl
			label={__('Import from file', 'ootb-openstreetmap')}
			help={__('Import locations from a previously exported JSON file.', 'ootb-openstreetmap')}
		>
			<FormFileUpload
				icon="database-add"
				iconPosition="left"
				variant="primary"
				isDestructive={hasMarkers}
				label={
					hasMarkers ?
						__('This map already includes locations. Uploading new locations will merge them with the existing ones.', 'ootb-openstreetmap')
						: null
				}
				text={__('Import locations', 'ootb-openstreetmap')}
				accept="application/json"
				onChange={
					(event) => {
						importMarkers(event)
					}
				}
			/>
		</BaseControl>
	);
}
