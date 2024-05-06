// noinspection NpmUsedModulesInstalled

import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {TextControl, Button} from '@wordpress/components';
import {Fragment} from '@wordpress/element';
import getNominatimSearchUrl from "../../Helpers/getNominatimSearchUrl";
import SearchResults from './SearchResults';

export default function SearchBox(props) {
    const [keywords, setKeywords] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [inputRef, setInputRef] = useState(null);

    const findMarkers = () => {
        if (keywords && keywords.length > 2) {
            fetch(getNominatimSearchUrl(keywords))
                .then(response => {
                    if (200 !== response.status) {
                        return;
                    }
                    return response.json();
                }).then(data => {
                setSearchResults(data);
            });
        }
    }

    const onTyping = (text) => {
        setKeywords(text);
    }

    const detectEnter = (e) => {
        if (!inputRef) {
            setInputRef(e);
        }
        if ('Enter' === e.key) {
            findMarkers();
        }
    }

    const onButtonClick = () => {
        if (searchResults && searchResults.length) {
            if (inputRef) {
                inputRef.target.focus();
                setSearchResults([]);
                setKeywords('');
            }
        } else {
            findMarkers();
        }
    }

    const icon = () => {
        if (keywords && searchResults && searchResults.length > 0) {
            return 'no';
        } else {
            return 'search';
        }
    }

    return (
        <Fragment>
            <div className="ootb-openstreetmap--searchbox">
                <TextControl
                    value={keywords}
                    onChange={onTyping}
                    onKeyDown={detectEnter}
                    placeholder={__('Enter keywords...', 'ootb-openstreetmap')}
                />
                <Button
                    onClick={onButtonClick}
                    icon={icon()}
                    showTooltip={true}
                    label={
                        (searchResults && searchResults.length > 0) ?
                            __('Clear results', 'ootb-openstreetmap') :
                            __('Find locations', 'ootb-openstreetmap')
                    }
                    disabled={!(keywords && keywords.length > 2)}
                />
            </div>
            <SearchResults
                {...props}
                setKeywords={setKeywords}
                searchResults={searchResults}
                setSearchResults={setSearchResults}
            />
        </Fragment>
    );
}
