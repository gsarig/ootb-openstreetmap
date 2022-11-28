// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import L from 'leaflet';
import {Marker, Popup} from 'react-leaflet';
import getIcon from '../Helpers/getIcon';
import getBounds from '../Helpers/getBounds';

import {__} from '@wordpress/i18n';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';

export default function Markers({props}) {
	const {
		attributes: {
			mapObj,
			markers,
		},
		setAttributes,
	} = props;

	const removeMarker = (e) => {
		const index = parseInt(e.target.getAttribute('dataIndex'));
		let updatedMarkers = [...markers];
		updatedMarkers.splice(index, 1);
		setAttributes({
			isDraggingMarker: false,
			markers: updatedMarkers,
			shouldUpdateBounds: true
		});
		getBounds(props, [], mapObj.leafletElement);
	}

	const startDragging = () => {
		if (markers) {
			setAttributes({isDraggingMarker: true});
		}
	}

	const isHovering = () => {
		startDragging();
	}

	let clickStarted = null;
	const onClickStart = () => {
		setAttributes({addingMarker: ''});
		clickStarted = new Date();
	}
	const onClickEnd = (e) => {
		const now = new Date();
		const duration = now - clickStarted;
		if (!clickStarted || false === !!duration) {
			e.target.openPopup();
		}
	}

	const markerIcon = L.icon(getIcon(props));

	return typeof markers !== "undefined" && markers.length ? markers.map((marker, index) => {
		return (
			<Marker
				key={index}
				position={[marker.lat, marker.lng]}
				icon={markerIcon}
				onMouseDown={onClickStart}
				onMouseUp={onClickEnd}
				onMouseOver={isHovering}
				draggable={true}
				onDragStart={startDragging}
				onDragEnd={(e) => {
					const newLatLng = e.target.getLatLng();
					let updatedMarkers = [...markers];
					updatedMarkers[index].lat = newLatLng.lat;
					updatedMarkers[index].lng = newLatLng.lng;
					setAttributes({
						isDraggingMarker: false,
						markers: updatedMarkers
					});
				}}
			>
				<Popup>
					<RichText
						multiline={true}
						value={marker.text}
						onChange={
							(content) => {
								let updatedMarkers = [...markers];
								updatedMarkers[index].text = content;
								setAttributes({
									markers: updatedMarkers
								});
							}
						}
						placeholder={__('Write something', 'ootb-openstreetmap')}
					/>
					<div className="ootb-openstreetmap--marker-remove">
						<Button
							onClick={removeMarker}
							dataindex={index}
							icon="trash"
							showTooltip={true}
							label={__('Remove this marker', 'ootb-openstreetmap')}
						>
							{__('Remove', 'ootb-openstreetmap')}
						</Button>
					</div>
				</Popup>
			</Marker>
		)
	}) : null;
}
