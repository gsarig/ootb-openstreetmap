// noinspection NpmUsedModulesInstalled,NpmUsedModulesInstalled

import {useState} from 'react';
import {Button} from '@wordpress/components';
import getMarkerFromElelement from '../../common/getMarkerFromElelement';

export default function SearchResults(props) {
    const {setMarker, setAddress, setLatitude, setLongitude, setMapUpdate, setKeywords, searchResults, setSearchResults} = props;

    const [searchCount, setSearchCount] = useState(0);
    const addMarker = (e) => {
        const newMarker = getMarkerFromElelement({searchCount, setSearchCount, searchResults}, e);
        setKeywords('');
        setSearchResults([]);
        setMarker(newMarker);
        setLatitude(newMarker?.lat ?? '');
        setLongitude(newMarker.lng ?? '');
        setAddress(newMarker?.textRaw ?? '');
        setMapUpdate(true);
    }
    const resultsList = () => {
        return searchResults.map((item, index) => {
            const {display_name} = item;
            return (
                <Button
                    key={index}
                    autoFocus={(index === 0)}
                    onClick={addMarker}
                    data-index={index}
                >{display_name}</Button>
            );
        });
    }

    return searchResults && searchResults.length ? (
        <div className="ootb-openstreetmap--search-results">
            {resultsList()}
        </div>
    ) : null;
}
