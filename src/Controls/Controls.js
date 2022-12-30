// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import MainControls from './MainControls';
import BehaviorControls from './BehaviorControls';
import DataControls from "./DataControls";

import {__} from '@wordpress/i18n';
const {PanelBody} = wp.components;
const {InspectorControls} = wp.blockEditor;

export default function Controls({props}) {
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
				initialOpen={false}
			>
				<DataControls props={props}/>
			</PanelBody>
		</InspectorControls>
	);
}
