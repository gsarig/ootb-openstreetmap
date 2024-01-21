// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {queryMarkers} from "../Helpers/queryMarkers";
import {BaseControl, Button, SelectControl, __experimentalNumberControl as NumberControl} from '@wordpress/components';

const {withSelect} = wp.data;

function ImportControl({props, postId}) {
	const {
		attributes: {
			queryArgs,
			serverSideRender,
		},
		setAttributes,
	} = props;

	const label = serverSideRender ? __('Automatically fetching from posts', 'ootb-openstreetmap') : __('Get from posts', 'ootb-openstreetmap');
	const help = serverSideRender ? __('Markers are automatically fetched from existing posts. By hitting "Stop syncing" the block will stop automatic updating with new markers, and you will be able to manually edit it.', 'ootb-openstreetmap') : __('Fetch locations from existing posts.', 'ootb-openstreetmap');
	const icon = serverSideRender ? 'controls-repeat' : 'admin-post';
	const variant = serverSideRender ? 'secondary' : 'primary';
	const buttonText = serverSideRender ? __('Stop syncing', 'ootb-openstreetmap') : __('Fetch locations', 'ootb-openstreetmap');

	const onClickHandler = () => {
		if (serverSideRender) {
			setAttributes({
				serverSideRender: false,
			});
		} else {
			queryMarkers(props, postId);
		}
	};

	return (
		<>
			<BaseControl
				label={label}
				help={help}
			>
				<div>
					{!serverSideRender ? (
						<>
							<SelectControl
								label={__('Post type', 'ootb-openstreetmap')}
								value={queryArgs.post_type ?? 'post'}
								options={ootbGlobal.postTypes}
								onChange={(selectPostType) => {
									setAttributes({
										queryArgs: {
											...queryArgs,
											post_type: selectPostType,
										},
									});
								}}
							/>
							<NumberControl
								label={__('Number of posts', 'ootb-openstreetmap')}
								value={queryArgs.posts_per_page ?? 100}
								min={1}
								max={2000}
								onChange={(number) => {
									setAttributes({
										queryArgs: {
											...queryArgs,
											posts_per_page: number,
										},
									});
								}}
							/>
						</>
					) : null}

					<Button
						icon={icon}
						iconPosition="left"
						variant={variant}
						onClick={onClickHandler}
					>
						{buttonText}
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
