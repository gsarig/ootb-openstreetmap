// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {registerPlugin} from '@wordpress/plugins';
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {__} from '@wordpress/i18n';
import './index.css';
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

if (ootbGlobal?.options?.geodata) {
    registerPlugin('ootb-custom-fields', {
        render: OOTBCustomFields,
    });
}
