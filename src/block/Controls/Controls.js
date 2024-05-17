// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import MainControls from './MainControls';
import BehaviorControls from './BehaviorControls';
import DataControls from './DataControls';

export default function Controls({props}) {
    const {
        attributes: {
            queryCustomFields
        },
    } = props;
    return (
        <InspectorControls>
            <PanelBody
                title={__('Main settings', 'ootb-openstreetmap')}
                initialOpen={true}
            >
                <MainControls props={props}/>
            </PanelBody>
            <PanelBody
                title={__('Map behavior', 'ootb-openstreetmap')}
                initialOpen={false}
            >
                <BehaviorControls props={props}/>
            </PanelBody>
            <PanelBody
                title={__('Map data', 'ootb-openstreetmap')}
                initialOpen={!!queryCustomFields}
            >
                <DataControls props={props}/>
            </PanelBody>
        </InspectorControls>
    );
}
