// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Fragment} from '@wordpress/element';
import QueryControl from "./QueryControl";
import ExportControl from "./ExportControl";
import ImportControl from "./ImportControl";

export default function DataControls({props}) {
	return (
		<Fragment>
			<QueryControl props={props}/>
			<ExportControl props={props}/>
			<ImportControl props={props}/>
		</Fragment>
	);
}
