import MainControls from './MainControls';
import BehaviorControls from './BehaviorControls';

// noinspection JSUnresolvedVariable
import {__} from '@wordpress/i18n';
// noinspection JSUnresolvedVariable
const {PanelBody} = wp.components;
// noinspection JSUnresolvedVariable
const {InspectorControls} = wp.blockEditor;

export default function Controls({props}) {
	// noinspection JSXNamespaceValidation
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
		</InspectorControls>
	);
}
