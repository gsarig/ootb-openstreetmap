import {useMapEvents} from 'react-leaflet';
import {isMobile, isSafari} from 'react-device-detect';
import getMarkerFromElelement from '../../common/getMarkerFromElelement';

const PIN_UPDATE_TIMEOUT = 300;
const PIN_HANG_TIMEOUT = PIN_UPDATE_TIMEOUT * 3;

export default function MapEvents(props) {
    const {
        draggingProps: {
            isDraggingMarker,
            setIsDraggingMarker,
            addingMarker,
            setAddingMarker
        },
        setMarker,
        setMapUpdate,
        setLatitude,
        setLongitude,
        setAddress
    } = props;

    let delay; // restore declaration of delay

    const isClicking = (e) => {
        if (e.originalEvent.target.nodeName.toLowerCase() === 'div') {
            setIsDraggingMarker(false);
        }
        if (!isDraggingMarker) {
            if (isMobile && !isSafari) {
                setAddingMarker('pinning');
            } else {
                delay = setTimeout(() => {
                    setAddingMarker('pinning');
                    setTimeout(() => setAddingMarker(''), PIN_HANG_TIMEOUT);
                }, PIN_UPDATE_TIMEOUT);
            }
        }
    };

    const isDragging = () => {
        clearTimeout(delay); // make sure to clear the timeout
        setAddingMarker('');
        setMapUpdate(false);
        setIsDraggingMarker(false);
    };

    const stopHovering = () => {
        setIsDraggingMarker(false);
    };

    const addMarker = (e) => {
        clearTimeout(delay); // make sure to clear the timeout
        if (addingMarker) {
            setMapUpdate(false);
            const newMarker = getMarkerFromElelement({}, e);
            setMarker(newMarker);
            setAddingMarker('');
            setLatitude(newMarker?.lat ?? '');
            setLongitude(newMarker.lng ?? '');
            setAddress(newMarker?.textRaw ?? '');
            setTimeout(() => setIsDraggingMarker(false), PIN_UPDATE_TIMEOUT * 2);
        }
    };

    useMapEvents({
        mouseup: addMarker,
        mousedown: isClicking,
        drag: isDragging,
        mouseout: stopHovering,
    });

    return null;
}
