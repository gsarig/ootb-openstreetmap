import Controls from './Controls/Controls';
import SearchBox from './Elements/SearchBox';
import LeafletMap from './Elements/LeafletMap';
import Alert from './Elements/Alert';
// noinspection NpmUsedModulesInstalled
import {useBlockProps} from '@wordpress/block-editor';

export default function edit(props) {
	const {attributes} = props;
	const {addingMarker} = attributes;
	const blockProps = useBlockProps({
		className: (addingMarker || '')
	});
	return (
		<div {...blockProps}>
			<Controls props={props}/>
			<SearchBox props={props}/>
			<LeafletMap props={props}/>
			<Alert props={props}/>
		</div>
	);
}
