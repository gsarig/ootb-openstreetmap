import { useEffect } from '@wordpress/element';
import { useMap } from 'react-leaflet/hooks';
import L from 'leaflet';

/**
 * Keeps mouse dragging of the map and its markers working when the editor canvas
 * is an iframe.
 *
 * From WordPress 7.1 the editor canvas is always an iframe (7.0 only uses one in
 * some modes, such as device preview). This script, and Leaflet with it, runs in
 * the parent wp-admin window, while the map's DOM lives in the iframe. Leaflet
 * follows a drag through mousemove and mouseup listeners on the parent document,
 * and the editor only partly passes iframe events on to it:
 * - mousemove is forwarded with the iframe's offset added to clientX/clientY, but
 *   Leaflet took the drag's start point from the original mousedown, in iframe
 *   coordinates, so every drag jumped by the iframe's offset;
 * - mouseup is not forwarded, so a drag never ended, and Leaflet then refused to
 *   start any other drag;
 * - the block wrapper is draggable, so pressing and moving inside the map also
 *   started an HTML5 block drag.
 *
 * The start-point correction relies on Leaflet internals (L.Draggable._dragging
 * and its _startPoint), checked against Leaflet 1.9.4. If an upgrade renames them,
 * the correction silently stops and drags jump again; the editor Playwright tests
 * cover this.
 */
export default function IframeMouseFix() {
	const map = useMap();

	useEffect(() => {
		const container = map.getContainer();
		const doc = container.ownerDocument;
		const frame = doc.defaultView.frameElement;
		let pressedInMap = false;

		// Registered after Leaflet's own mousedown handlers (on the marker icon, or
		// earlier on this same container), so a drag that just started already has
		// its start point stored, in iframe coordinates.
		const onMouseDown = (e) => {
			pressedInMap = true;
			const active = L.Draggable._dragging;
			if (
				frame &&
				active &&
				active._startPoint &&
				active._startPoint.x === e.clientX &&
				active._startPoint.y === e.clientY
			) {
				const rect = frame.getBoundingClientRect();
				active._startPoint.x += rect.left;
				active._startPoint.y += rect.top;
			}
		};
		const onDragStart = (e) => {
			if (pressedInMap) {
				e.preventDefault();
				e.stopPropagation();
			}
		};
		// Only a press that started in this map is passed on, so other clicks in
		// the editor, and other maps on the page, are left alone.
		const onMouseUp = () => {
			if (!pressedInMap) {
				return;
			}
			pressedInMap = false;
			if (frame) {
				document.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
			}
		};
		// A release outside the iframe reaches the parent document directly.
		const onParentMouseUp = () => {
			pressedInMap = false;
		};

		container.addEventListener('mousedown', onMouseDown);
		doc.addEventListener('dragstart', onDragStart, { capture: true });
		doc.addEventListener('mouseup', onMouseUp);
		if (frame) {
			window.addEventListener('mouseup', onParentMouseUp);
		}

		return () => {
			container.removeEventListener('mousedown', onMouseDown);
			doc.removeEventListener('dragstart', onDragStart, { capture: true });
			doc.removeEventListener('mouseup', onMouseUp);
			window.removeEventListener('mouseup', onParentMouseUp);
		};
	}, [map]);

	return null;
}
