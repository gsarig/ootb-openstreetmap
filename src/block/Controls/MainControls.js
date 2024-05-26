// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {Fragment} from '@wordpress/element';
import {RangeControl} from '@wordpress/components';
import IconControl from './IconControl';
import ProviderControl from './ProviderControl';
import TypeControl from './TypeControl';

export default function MainControls({props}) {
	const {
		attributes: {
			mapHeight,
			zoom,
		},
		setAttributes,
	} = props;

	return (
		<Fragment>
			<ProviderControl props={props}/>
			<TypeControl props={props}/>
			<RangeControl
				label={__('Height (pixels)', 'ootb-openstreetmap')}
				value={mapHeight}
				onChange={
					(pixels) => {
						setAttributes({mapHeight: pixels})
					}
				}
				min={50}
				max={1000}
			/>
			<RangeControl
				label={__('Zoom', 'ootb-openstreetmap')}
				value={zoom}
				onChange={
					(value) => {
						setAttributes({
							zoom: value,
							shouldUpdateZoom: true
						})
					}
				}
				min={0}
				max={18}
			/>
			<IconControl props={props}/>
		</Fragment>
	);
}
