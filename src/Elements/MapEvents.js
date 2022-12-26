import {useMapEvents} from "react-leaflet/hooks";
import {isMobile, isSafari} from "react-device-detect";
import getBounds from "../Helpers/getBounds";
// noinspection NpmUsedModulesInstalled
import {useEffect} from '@wordpress/element';
import getMarkerFromElelement from "../Helpers/getMarkerFromElelement";

export default function MapEvents({props}) {
	const {
		attributes: {
			mapObj,
			markers,
			zoom,
			showDefaultBounds,
			shouldUpdateZoom,
			shouldUpdateBounds,
			addingMarker,
			isDraggingMarker,
		},
		setAttributes
	} = props;
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
			const newMarker = getMarkerFromElelement(props, e);
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
	const map = useMapEvents({
		// Available events: https://leafletjs.com/reference.html#evented
		mouseup: (e) => {
			addMarker(e);
		},
		mousedown: (e) => {
			isClicking(e);
		},
		drag: (e) => {
			isDragging(e);
		},
		zoomanim: (e) => {
			changeZoom(e);
		},
		popupclose: () => {
			closePopupActions();
		}
	});
	useEffect(() => {
		if (true === showDefaultBounds) {
			// noinspection JSUnresolvedVariable
			setAttributes({
				bounds: ootbGlobal.defaultLocation,
				showDefaultBounds: false
			});
		}
		if (true === shouldUpdateBounds) {
			getBounds(props, [], map);
			setAttributes({
				shouldUpdateBounds: false
			});
		}
		if ('undefined' !== typeof map && ('undefined' === typeof mapObj || !mapObj.length || mapObj !== map)) {
			setAttributes({mapObj: map});
		}
		if (true === shouldUpdateZoom && zoom !== map.getZoom()) {
			map.setZoom(zoom);
			setAttributes({shouldUpdateZoom: false});
		}
	});
	return null;
}
