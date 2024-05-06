// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import SearchBox from "./SearchBox";
import {MapContainer, Marker, TileLayer} from "react-leaflet";
import L from 'leaflet';
import MapEvents from './MapEvents';
import MapUpdate from './MapUpdate';
import getFallbackIcon from '../../Helpers/getFallbackIcon';
import getBoundsCenter from '../../Helpers/getBoundsCenter';
import {useState} from 'react';

export default function MapControl(props) {
    const {marker, addingMarker, setAddingMarker} = props;
    const defaultPosition = getBoundsCenter(ootbGlobal.defaultLocation);
    const markerLat = marker?.lat ? parseFloat(marker.lat) : null;
    const markerLng = marker?.lng ? parseFloat(marker.lng) : null;
    const markerPosition = markerLat && markerLng ? [markerLat, markerLng] : null;

    const [isDraggingMarker, setIsDraggingMarker] = useState(false);
    const draggingProps = {isDraggingMarker, setIsDraggingMarker, addingMarker, setAddingMarker};

    const markerIcon = L.icon(
        {
            iconUrl: getFallbackIcon(),
            iconAnchor: [12, 41]
        }
    );

    return (
        <>
            <SearchBox {...props}/>
            <MapContainer
                center={defaultPosition}
                zoom={6}
                scrollWheelZoom={false}
                style={
                    {
                        height: '250px'
                    }
                }
            >
                <MapEvents {...props} draggingProps={draggingProps}/>
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />
                {markerPosition ?
                    <>
                        <Marker
                            position={markerPosition}
                            icon={markerIcon}
                        />
                        <MapUpdate
                            {...props}
                            markerPosition={markerPosition}
                        />
                    </>
                    : null}

            </MapContainer>
        </>
    );
}
