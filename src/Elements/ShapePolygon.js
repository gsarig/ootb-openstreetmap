// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Polygon, Popup} from 'react-leaflet';

import {__} from '@wordpress/i18n';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';

export default function ShapePolygon({props, styles}) {
	const {
		attributes: {
			markers,
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
	const setShapeText = (content) => {
		setAttributes({
			shapeText: content
		});
	}
	return (
		<Polygon
			positions={markers}
			pathOptions={styles}
		>
			<Popup>
				<RichText
					multiline={true}
					value={shapeText}
					onChange={setShapeText}
					placeholder={__('Write something', 'ootb-openstreetmap')}
				/>
				<div className="ootb-openstreetmap--marker-icon-container ootb-openstreetmap--marker-remove ootb-openstreetmap--shape-controls">
					<Button
						onClick={removeShape}
						icon="trash"
						isDestructive={true}
						variant="secondary"
						showTooltip={true}
						isSecondary
						label={__('Remove this shape', 'ootb-openstreetmap')}
					/>
				</div>
			</Popup>
		</Polygon>
	);
}
