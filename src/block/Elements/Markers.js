// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import L from 'leaflet';
import {Marker, Popup} from 'react-leaflet';
import getIcon from '../Helpers/getIcon';
import getBounds from '../Helpers/getBounds';
import getMarkerIndex from '../Helpers/getMarkerIndex';

import {__} from '@wordpress/i18n';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';
import IconControl from '../Controls/IconControl';

export default function Markers({props}) {
	const {
		attributes: {
			mapObj,
			markers,
			isDraggingMarker,
		},
		setAttributes,
	} = props;

	const removeMarker = (e) => {

		const element = e.target;
		let index;

		// Check if dataIndex attribute exists in the element (for backwards compatibility)
		if (element.hasAttribute('dataIndex')) {
			index = element.getAttribute('dataIndex');
		} else {
			// Find the immediate parent which should be a "button" element
			const parentElement = element.parentElement;

			// Make sure the parentElement is a "button"
			if (parentElement && parentElement.tagName.toLowerCase() === 'button') {
				index = parentElement.getAttribute('dataIndex');
			}
		}

		let updatedMarkers = [...markers];
		updatedMarkers.splice(index, 1);
		setAttributes({
			isDraggingMarker: false,
			markers: updatedMarkers,
			shouldUpdateBounds: true
		});
		getBounds(props, [], mapObj.leafletElement);
	}

	const isHovering = () => {
		if (markers) {
			setAttributes({
				isDraggingMarker: true,
			});
		}
	}

	let clickStarted = null;
	const onClickStart = () => {
		clickStarted = new Date();
		setAttributes({
			addingMarker: ''
		});
	}
	const onClickEnd = (e) => {
		if (true !== isDraggingMarker) {
			const now = new Date();
			const duration = now - clickStarted;
			if (!clickStarted || false === !!duration) {
				e.target.openPopup();
			}
		}
	}
	const startDragging = () => {
		setAttributes({
			isDraggingMarker: true
		});
	}
	const stopDragging = (e) => {
		const newLatLng = e.target.getLatLng();
		const index = getMarkerIndex(e, markers);
		const updatedMarkers = [...markers];
		if (updatedMarkers[index]) {
			updatedMarkers[index].lat = newLatLng.lat.toString();
			updatedMarkers[index].lng = newLatLng.lng.toString();
			setAttributes({
				markers: updatedMarkers,
				shouldUpdateBounds: true
			});
			getBounds(props, [], mapObj.leafletElement);
		}
		setAttributes({
			isDraggingMarker: false
		});
	}
	const markerIcon = (index) => L.icon(getIcon(props, index));
	return typeof markers !== "undefined" && markers.length ? markers.map((marker, index) => {
		return (
			<Marker
				key={index}
				markerId={marker.id}
				position={[marker.lat, marker.lng]}
				icon={markerIcon(index)}
				draggable={true}
				eventHandlers={
					{
						mousedown: (e) => {
							onClickStart(e);
						},
						mouseup: (e) => {
							onClickEnd(e);
						},
						mouseover: (e) => {
							isHovering(e);
						},
						dragstart: (e) => {
							startDragging(e);
						},
						dragend: (e) => {
							stopDragging(e);
						}
					}
				}
			>
				<Popup>
					<RichText
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
					<div className="ootb-openstreetmap--marker-icon-container">
						<IconControl props={props} index={index}/>
						<Button
							onClick={removeMarker}
							dataindex={index}
							icon="trash"
							isDestructive={true}
							variant="secondary"
							showTooltip={true}
							label={__('Remove this marker', 'ootb-openstreetmap')}
						/>
					</div>
				</Popup>
			</Marker>
		)
	}) : null;
}
