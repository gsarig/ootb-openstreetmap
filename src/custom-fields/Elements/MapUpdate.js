import {useMap} from "react-leaflet";

export default function MapUpdate(props) {
    const {mapUpdate, markerPosition} = props;
    if (mapUpdate) {
        const map = useMap();
        map.fitBounds([markerPosition]);
        map.setZoom(6);
    }
    return null
}
