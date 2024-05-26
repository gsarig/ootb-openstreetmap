// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import {__} from '@wordpress/i18n';
import {TextareaControl} from '@wordpress/components';

export default function MapValues(props) {
    const {latitude, longitude, address, setAddress, setMapUpdate} = props;

    if (!latitude || latitude === 0 || !longitude || longitude === 0) {
        return;
    }

    const updateAddress = (value) => {
        setAddress(value);
        setMapUpdate(false);
    }
    return (
        <>
            <table>
                <tbody>
                <tr>
                    <td><strong>{__('Latitude:', 'ootb-openstreetmap')} </strong></td>
                    <td><code>{latitude}</code></td>
                </tr>
                <tr>
                    <td><strong>{__('Longitude:', 'ootb-openstreetmap')} </strong></td>
                    <td><code>{longitude}</code></td>
                </tr>
                </tbody>
            </table>
            <br/>
            <TextareaControl
                label={__('Address', 'ootb-openstreetmap')}
                value={address}
                onChange={updateAddress}
                placeholder={__('Enter address', 'ootb-openstreetmap')}
            />
        </>
    );
}
