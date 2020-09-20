import {Map, TileLayer} from 'react-leaflet';
import Controls from "./Elements/Controls";
import Markers from "./Elements/Markers";
import getBounds from "./Helpers/getBounds";
import centerMap from "./Helpers/centerMap";
import {isMobile, isSafari} from 'react-device-detect';
import SearchBox from "./Elements/SearchBox";
import Alert from "./Elements/Alert";

// noinspection JSUnresolvedVariable
const {useRef} = wp.element;

export default function edit(props) {
	const {
		className,
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
	if ('undefined' !== typeof inputRef.current && !mapObj) {
		setAttributes({mapObj: inputRef.current});
	}

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
			const newMarker = {
				lat: e.latlng.lat,
				lng: e.latlng.lng,
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
		<div className={className + (addingMarker || '')}>
			<SearchBox props={props}/>
			<Controls props={props}/>
			<Alert props={props}/>
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
				<TileLayer
					url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
					attribution="&copy; <a href=&quot;http://osm.org/copyright&quot;>OpenStreetMap</a> contributors"
				/>
				<Markers props={props}/>
			</Map>
		</div>
	);
}
