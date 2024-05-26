// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {registerPlugin} from '@wordpress/plugins';
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {__} from '@wordpress/i18n';
import {select, subscribe} from '@wordpress/data';
import './index.css';
import supportsGeodata from "../common/supportsGeodata";
import MapCustomField from "./Elements/MapCustomField";

const OOTBCustomFields = () => (
    <PluginDocumentSettingPanel
        name="ootb-custom-fields"
        title={__('Location', 'ootb-openstreetmap')}
        className="ootb-custom-fields"
    >
        <MapCustomField/>
    </PluginDocumentSettingPanel>
);
let unsubscribe = subscribe(function () {
    const postType = select('core/editor').getCurrentPostType();

    if (supportsGeodata(postType)) {
        return;
    }

    registerPlugin('ootb-custom-fields', {
        render: OOTBCustomFields,
    });

    unsubscribe();
});
