// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Fragment} from '@wordpress/element';
import QueryControl from './QueryControl';
import ExportControl from './ExportControl';
import ImportControl from './ImportControl';

export default function DataControls({props}) {
    const {
        attributes: {
            showMapData
        },
    } = props;
    return (
        <Fragment>
            <QueryControl props={props}/>
            {showMapData &&
                <>
                    <ExportControl props={props}/>
                    <ImportControl props={props}/>
                </>
            }
        </Fragment>
    );
}
