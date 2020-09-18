import {Map, TileLayer} from 'react-leaflet';
import Controls from "./Elements/Controls";
import Markers from "./Elements/Markers";
import getBounds from "./Helpers/getBounds";
import centerMap from "./Helpers/centerMap";
import {isMobile, isSafari} from 'react-device-detect';

const {__} = wp.i18n;

export default function edit(props) {
	const {
		className,
		attributes: {
			markers,
			zoom,
			addingMarker,
			mapHeight,
			isDraggingMarker,
			alert,
		},
		setAttributes,
	} = props;

	const timeout = 300;
	let delay;
	const isClicking = () => {
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
		if (false === isDraggingMarker) {
			clearTimeout(delay);
			setAttributes({addingMarker: ''});
		}
	}

	const addMarker = (e) => {
		clearTimeout(delay);
		if (!!addingMarker) {
			setAttributes({
				markers: [
					...markers,
					{
						lat: e.latlng.lat,
						lng: e.latlng.lng,
						text: '',
					}
				],
				isDraggingMarker: false,
			});
			getBounds(props);
		}
		setAttributes({
			addingMarker: '',
		});
	}

	const changeZoom = (e) => {
		setAttributes({zoom: e.zoom});
	}

	const closePopupActions = () => {
		setAttributes({isDraggingMarker: false});
	}

	let setAlert = '';
	if (' pinning' === addingMarker) {
		setAlert = __('Release to drop a marker here', 'ootb-openstreetmap');
	}
	setAttributes({alert: setAlert});

	return (
		<div className={className + addingMarker}>
			<Controls props={props}/>
			{alert ?
				<div className="ootb-openstreetmap--alert">{alert}</div>
				: null}
			<Map
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
