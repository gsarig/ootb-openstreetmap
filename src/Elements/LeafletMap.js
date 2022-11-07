import centerMap from '../Helpers/centerMap';
import TileProvider from './TileProvider';
import Markers from './Markers';
import {Map} from 'react-leaflet';
import {isMobile, isSafari} from 'react-device-detect';
import getBounds from '../Helpers/getBounds';
import {useRef, useEffect} from '@wordpress/element';

export default function LeafletMap({props}) {
	const {
		attributes: {
			mapObj,
			markers,
			zoom,
			addingMarker,
			mapHeight,
			isDraggingMarker,
		},
		setAttributes,
	} = props;

	const inputRef = useRef();

	useEffect(() => {
		if ('undefined' !== typeof inputRef.current && ('undefined' === typeof mapObj || !mapObj.length || mapObj !== inputRef.current)) {
			setAttributes({mapObj: inputRef.current});
		}
	});

	const timeout = 300;
	let delay;
	const isClicking = (e) => {
		const elementClicked = e.originalEvent.target.nodeName.toLowerCase();
		if ('div' === elementClicked) {
			setAttributes({isDraggingMarker: false});
		}
		if (false === isDraggingMarker) {
			if (isMobile && !isSafari) {
				setAttributes({addingMarker: ' pinning'});
			} else {
				delay = setTimeout(function () {
					setAttributes({addingMarker: ' pinning'});
					setTimeout(function () { // If hangs for too long, stop it.
						setAttributes({addingMarker: ''});
					}, timeout * 3);
				}, timeout);
			}
		}
	}

	const isDragging = () => {
		clearTimeout(delay);
		setAttributes({addingMarker: '', isDraggingMarker: false});
	}

	const addMarker = (e) => {
		clearTimeout(delay);
		if (!!addingMarker) {
			const lat = e.latlng.lat;
			const lng = e.latlng.lng;
			const newMarker = {
				lat: lat.toString(),
				lng: lng.toString(),
				text: '',
			};
			setAttributes({
				markers: [
					...markers,
					newMarker,
				],
				addingMarker: '',
			});
			getBounds(props, newMarker, e.target);
			setTimeout(function () {
				setAttributes({isDraggingMarker: false});
			}, timeout * 2);
		}
	}
	const changeZoom = (e) => {
		setAttributes({zoom: e.zoom});
	}

	const closePopupActions = () => {
		setAttributes({isDraggingMarker: false});
	}

	// noinspection JSXNamespaceValidation
	return (
		<Map
			ref={inputRef}
			center={centerMap(props)}
			zoom={zoom}
			onMouseUp={addMarker}
			onMouseDown={isClicking}
			onDrag={isDragging}
			onViewportChanged={changeZoom}
			onPopupClose={closePopupActions}
			style={
				{
					height: mapHeight + 'px'
				}
			}
		>
			<TileProvider props={props}/>
			<Markers props={props}/>
		</Map>
	);
}
