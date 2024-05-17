// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {BaseControl, SelectControl} from '@wordpress/components';
import QuerySyncButton from './QuerySyncButton';

export default function QueryControl({props}) {
	const {
		attributes: {
			queryArgs,
			serverSideRender,
		},
		setAttributes,
	} = props;

	const label = serverSideRender ? __('Automatically fetching from existing entries', 'ootb-openstreetmap') : __('Get from existing entries', 'ootb-openstreetmap');
	const help = serverSideRender ? __('Markers are automatically fetched from existing entries. By hitting "Stop syncing" the block will stop automatic updating with new markers, and you will be able to manually edit it.', 'ootb-openstreetmap') : __('Fetch locations from existing entries.', 'ootb-openstreetmap');
	const variant = serverSideRender ? 'secondary' : 'primary';

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
						</>
					) : null}

					<QuerySyncButton props={props} variant={variant}/>
				</div>
			</BaseControl>
			<hr/>
		</>
	);
}
