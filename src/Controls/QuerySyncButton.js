// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {queryMarkers} from "../Helpers/queryMarkers";
import {Button} from '@wordpress/components';

const {withSelect} = wp.data;

function QuerySyncButton({props, postId, variant}) {
	const {
		attributes: {
			serverSideRender,
		},
		setAttributes,
	} = props;
	const icon = serverSideRender ? 'controls-repeat' : 'admin-post';
	const buttonText = serverSideRender ? __('Stop syncing', 'ootb-openstreetmap') : __('Fetch locations', 'ootb-openstreetmap');
	const onClickHandler = () => {
		if (!serverSideRender) {
			setAttributes({
				markers: [],
				serverSideRender: true,
				bounds: [
					[
						37.97155174977503,
						23.72656345367432
					]
				],
			});
		} else {
			queryMarkers(props, postId);
		}
	};

	return (
		<Button
			icon={icon}
			iconPosition="left"
			variant={variant}
			onClick={onClickHandler}
		>
			{buttonText}
		</Button>
	)
}

export default withSelect((select) => {
	return {
		postId: select("core/editor").getCurrentPostId(),
	};
})(QuerySyncButton);
