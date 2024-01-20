// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import validMarkers from "../Helpers/validMarkers";
import removeDuplicates from "../Helpers/removeDuplicates";
import {BaseControl, Button} from '@wordpress/components';

const {withSelect} = wp.data;

function ImportControl({props, postId}) {
	const {
		attributes: {
			markers,
		},
		setAttributes,
	} = props;
	const getMarkers = () => {
		return jQuery.ajax({
			url: ootbGlobal.ajax_url,
			method: 'POST',
			data: {
				action: 'ootb_get_markers',
				post_id: postId,
			},
		}).then(response => {
			const queriedMarkers = validMarkers(JSON.parse(response));
			if (!queriedMarkers) {
				return;
			}
			const uniqueMarkers = removeDuplicates(queriedMarkers, markers);

			if (uniqueMarkers.length === 0) {
				return;
			}
			setAttributes({
				markers: markers.concat(uniqueMarkers),
				shouldUpdateBounds: true,
			});
		});
	};
	return (
		<>
			<BaseControl
				label={__('Fetch from posts', 'ootb-openstreetmap')}
				help={__('Fetch locations from existing posts.', 'ootb-openstreetmap')}
			>
				<div>
					<Button
						icon="admin-post"
						iconPosition="left"
						variant="primary"
						onClick={getMarkers}
					>
						{__('Fetch locations', 'ootb-openstreetmap')}
					</Button>
				</div>
			</BaseControl>
			<hr/>
		</>
	);
}

export default withSelect((select, {props}) => {
	return {
		postId: select("core/editor").getCurrentPostId(),
	};
})(ImportControl);
