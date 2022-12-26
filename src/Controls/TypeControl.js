// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {SelectControl, ToggleControl, ColorPicker, BaseControl} from '@wordpress/components';
import {Fragment} from '@wordpress/element';

export default function TypeControl({props}) {
	const {
		attributes: {
			mapType,
			showMarkers,
			shapeColor,
		},
		setAttributes,
	} = props;

	const mapTypes = [
		{
			label: __('Markers', 'ootb-openstreetmap'),
			value: 'marker',
		},
		{
			label: __('Polygon', 'ootb-openstreetmap'),
			value: 'polygon',
		},
	];
	const setMapType = type => {
		setAttributes({
			mapType: type,
		})
	}
	const setShapeColor = color => {
		setAttributes({
			shapeColor: color,
		})
	}
	return (
		<Fragment>
			<SelectControl
				label={__('Map type', 'ootb-openstreetmap')}
				value={mapType}
				options={mapTypes}
				onChange={setMapType}
				help={__('How the map should display its locations.', 'ootb-openstreetmap')}
			/>
			{'marker' !== mapType ?
				<Fragment>
					<ToggleControl
						label={__('Show Markers', 'ootb-openstreetmap')}
						checked={!!showMarkers}
						onChange={() => setAttributes({showMarkers: !showMarkers})}
						help={
							!!showMarkers ?
								__('Show the markers on the frontend.', 'ootb-openstreetmap') :
								__('Hide the markers on the frontend (does not affect the Backend).', 'ootb-openstreetmap')
						}
					/>
					<BaseControl
						label={__('Shape color', 'ootb-openstreetmap')}
					>
						<ColorPicker
							color={shapeColor}
							onChange={setShapeColor}
							enableAlpha={false}
							defaultValue={null}
						/>
					</BaseControl>
				</Fragment>
				: null}
		</Fragment>
	);
}
