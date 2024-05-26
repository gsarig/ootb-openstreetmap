// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {useEffect, useState} from 'react';
import {withSelect, withDispatch} from '@wordpress/data';
import {compose} from '@wordpress/compose';
import MapControl from './MapControl';
import MapValues from './MapValues';

function MapCustomField({geoAddress, geoLatitude, geoLongitude, setMetaValues}) {
    const [marker, setMarker] = useState({lat: geoLatitude, lng: geoLongitude});
    const [latitude, setLatitude] = useState(geoLatitude);
    const [longitude, setLongitude] = useState(geoLongitude);
    const [address, setAddress] = useState(geoAddress);
    const [mapUpdate, setMapUpdate] = useState(false);
    const [addingMarker, setAddingMarker] = useState('');
    const props = {
        marker,
        setMarker,
        latitude,
        setLatitude,
        longitude,
        setLongitude,
        address,
        setAddress,
        mapUpdate,
        setMapUpdate,
        addingMarker,
        setAddingMarker
    };

    useEffect(() => {
        setMapUpdate(true);
    }, []);
    useEffect(() => {
        setMetaValues({
            'geo_address': address,
            'geo_latitude': latitude,
            'geo_longitude': longitude
        });
    }, [latitude, longitude, address]);

    return (
        <div className={`ootb-openstreetmap--custom-fields-container ${addingMarker}`}>
            <MapControl {...props} />
            <MapValues {...props} />
        </div>
    );
}

export default compose([
    withSelect((select) => {
        const {getEditedPostAttribute} = select('core/editor');
        const meta = getEditedPostAttribute('meta');
        return {
            meta,
            geoAddress: meta['geo_address'],
            geoLatitude: meta['geo_latitude'],
            geoLongitude: meta['geo_longitude'],
        };
    }),
    withDispatch((dispatch, {meta}) => {
        const {editPost} = dispatch('core/editor');
        return {
            setMetaValues: (values) => {
                editPost({meta: {...meta, ...values}});
            }
        }
    })
])(MapCustomField);