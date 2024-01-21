import {__} from '@wordpress/i18n';
import Controls from './Controls/Controls';
import SearchBox from './Elements/SearchBox';
import LeafletMap from './Elements/LeafletMap';
import Alert from './Elements/Alert';
import {Button} from '@wordpress/components';

// noinspection NpmUsedModulesInstalled
import {useBlockProps} from '@wordpress/block-editor';

export default function edit(props) {
	const {
		attributes: {
			addingMarker,
			queryArgs,
			serverSideRender
		},
		setAttributes,
	} = props;
	const blockProps = useBlockProps({
		className: (addingMarker || '')
	});
	const selectedPostTypeName = () => {
		let label = '';
		if (ootbGlobal && ootbGlobal.postTypes) {
			const postTypeObj = ootbGlobal.postTypes.find(obj => obj.value === queryArgs?.post_type);

			if (postTypeObj) {
				label = postTypeObj.label;
			}
		}
		return label;
	};
	return (
		<div {...blockProps}>
			<Controls props={props}/>
			{serverSideRender ? (
				<div className="ootb-server-side-rendered">
					<span>
						{__('Updates automatically from the latest ' + queryArgs?.posts_per_page + ' ' + selectedPostTypeName(), 'ootb-openstreetmap')}
					</span>
					<div>
						<Button
							icon="controls-repeat"
							iconPosition="left"
							variant="primary"
							onClick={
								() => {
									setAttributes({
										serverSideRender: false,
									});
								}
							}
						>
							{__('Stop syncing', 'ootb-openstreetmap')}
						</Button>
					</div>
				</div>
			) : null}
			<SearchBox props={props}/>
			<LeafletMap props={props}/>
			<Alert props={props}/>
		</div>
	);
}
