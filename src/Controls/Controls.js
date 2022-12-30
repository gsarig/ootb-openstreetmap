// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import MainControls from './MainControls';
import BehaviorControls from './BehaviorControls';
import DataControls from "./DataControls";

import {__} from '@wordpress/i18n';
import {PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';

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
