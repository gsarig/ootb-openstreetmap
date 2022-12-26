// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {Polyline, Popup} from 'react-leaflet';

import {__} from '@wordpress/i18n';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';

export default function ShapePolyline({props, styles}) {
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
		<Polyline
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
		</Polyline>
	);
}
