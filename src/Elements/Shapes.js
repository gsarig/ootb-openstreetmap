// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Polygon, Popup} from 'react-leaflet';

import {__} from '@wordpress/i18n';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';

export default function Shapes({props}) {
	const {
		attributes: {
			markers,
			isDraggingMarker,
			shapeColor,
			shapeText,
		},
		setAttributes,
	} = props;

	const removeShape = () => {
		setAttributes({
			isDraggingMarker: false,
			markers: [],
			shapeText: '',
			zoom: 8,
			shouldUpdateZoom: true,
		});
	}
	const getShapeColor = {fillColor: shapeColor, color: shapeColor}
	const setShapeText = (content) => {
		setAttributes({
			shapeText: content
		});
	}
	return (true !== isDraggingMarker && markers.length ?
			<Polygon
				positions={markers}
				pathOptions={getShapeColor}
			>
				<Popup>
					<RichText
						multiline={true}
						value={shapeText}
						onChange={setShapeText}
						placeholder={__('Write something', 'ootb-openstreetmap')}
					/>
					<div className="ootb-openstreetmap--marker-remove">
						<Button
							onClick={removeShape}
							icon="trash"
							showTooltip={true}
							label={__('Remove this shape', 'ootb-openstreetmap')}
						>
							{__('Remove', 'ootb-openstreetmap')}
						</Button>
					</div>
				</Popup>
			</Polygon>
			: null
	);
}
