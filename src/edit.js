// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import Controls from './Controls/Controls';
import SearchBox from './Elements/SearchBox';
import LeafletMap from './Elements/LeafletMap';
import Alert from './Elements/Alert';
import {useBlockProps} from '@wordpress/block-editor';
import QuerySyncButton from "./Controls/QuerySyncButton";

export default function edit(props) {
	const {
		attributes: {
			addingMarker,
			queryArgs,
			serverSideRender
		},
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
						{__('Updates automatically from the latest ' + selectedPostTypeName(), 'ootb-openstreetmap')}
					</span>
					<div>
						<QuerySyncButton props={props} variant="primary"/>
					</div>
				</div>
			) : null}
			<SearchBox props={props}/>
			<LeafletMap props={props}/>
			<Alert props={props}/>
		</div>
	);
}
