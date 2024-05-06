// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {useState} from 'react';
import MapControl from "./MapControl";
import {TextControl, TextareaControl} from '@wordpress/components';

export default function MapCustomField() {
    const [marker, setMarker] = useState(null);
    const [address, setAddress] = useState(null);
    const [mapUpdate, setMapUpdate] = useState(false);
    const [addingMarker, setAddingMarker] = useState('');

    const updateAddress = (value) => {
        setMapUpdate(false);
        setAddress(value);
    }
    const props = {marker, setMarker, mapUpdate, setMapUpdate, addingMarker, setAddingMarker};
    const addressVal = address && !mapUpdate ? address : marker?.textRaw;
    return (
        <div
            className={'ootb-openstreetmap--custom-fields-container ' + addingMarker}
        >
            <MapControl {...props}/>
            <TextControl
                label={__('Latitude', 'ootb-openstreetmap')}
                value={marker?.lat ?? ''}
                readOnly
            />
            <TextControl
                label={__('Longitude', 'ootb-openstreetmap')}
                value={marker?.lng ?? ''}
                readOnly
            />
            <TextareaControl
                label={__('Address', 'ootb-openstreetmap')}
                value={addressVal ?? ''}
                onChange={updateAddress}
            />
        </div>
    );
}
