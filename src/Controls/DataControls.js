// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Fragment} from '@wordpress/element';
import ExportControl from "./ExportControl";
import ImportControl from "./ImportControl";

export default function DataControls({props}) {
	return (
		<Fragment>
			<ExportControl props={props}/>
			<ImportControl props={props}/>
		</Fragment>
	);
}
