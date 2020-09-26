import Controls from "./Controls/Controls";
import SearchBox from "./Elements/SearchBox";
import LeafletMap from "./Elements/LeafletMap";
import Alert from "./Elements/Alert";

export default function edit(props) {
	const {
		className,
		attributes: {
			addingMarker,
		},
	} = props;

	// noinspection JSXNamespaceValidation
	return (
		<div className={className + (addingMarker || '')}>
			<Controls props={props}/>
			<SearchBox props={props}/>
			<LeafletMap props={props}/>
			<Alert props={props}/>
		</div>
	);
}
