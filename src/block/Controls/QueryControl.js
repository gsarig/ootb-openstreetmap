// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {BaseControl, SelectControl} from '@wordpress/components';
import getGeoPostTypes from "../../common/getGeoPostTypes";
import QuerySyncButton from './QuerySyncButton';

export default function QueryControl({props}) {
    const {
        attributes: {
            queryArgs,
            serverSideRender,
            showMapData,
            queryCustomFields,
        },
        setAttributes,
    } = props;

    const variant = serverSideRender ? 'secondary' : 'primary';

    let label = __('Get from existing entries', 'ootb-openstreetmap');
    let help = __('Fetch locations from existing posts or post types containing at least one map block.', 'ootb-openstreetmap');
    if (queryCustomFields) {
        label = __('Gets posts with locations', 'ootb-openstreetmap');
        help = __('Markers are automatically fetched from existing posts or post types with locations assigned to them.', 'ootb-openstreetmap');
    } else if (serverSideRender) {
        label = __('Gets posts containing the block', 'ootb-openstreetmap');
        help = __('Markers are automatically fetched from existing posts or post types containing at least one map block. By hitting "Stop syncing" the block will stop automatic updating with new markers, and you will be able to manually edit it.', 'ootb-openstreetmap');
    }

    const selectOptions = queryCustomFields ? getGeoPostTypes() : ootbGlobal?.postTypes;
    return (
        <>
            <BaseControl
                label={label}
                help={help}
            >
                <div>
                    {((!serverSideRender || queryCustomFields) && selectOptions.length > 1) && (
                        <>
                            <SelectControl
                                label={__('Post type', 'ootb-openstreetmap')}
                                value={queryArgs.post_type ?? 'post'}
                                options={selectOptions}
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
                    )}
                    {showMapData &&
                        <QuerySyncButton props={props} variant={variant}/>
                    }
                </div>
            </BaseControl>
            <hr/>
        </>
    );
}
