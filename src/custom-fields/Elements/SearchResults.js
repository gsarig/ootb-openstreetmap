// noinspection NpmUsedModulesInstalled,NpmUsedModulesInstalled

import {useState} from 'react';
import {Button} from '@wordpress/components';
import getMarkerFromElelement from "../../Helpers/getMarkerFromElelement";

export default function SearchResults({setKeywords, searchResults, setSearchResults, setMarker, setMapUpdate}) {
    const [searchCount, setSearchCount] = useState(0);
    const addMarker = (e) => {
        const newMarker = getMarkerFromElelement({searchCount, setSearchCount, searchResults}, e);
        setMarker(newMarker);
        setKeywords('');
        setSearchResults([]);
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
