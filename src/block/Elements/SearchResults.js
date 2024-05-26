// noinspection NpmUsedModulesInstalled,NpmUsedModulesInstalled

import {useState} from 'react';
import getBounds from '../Helpers/getBounds';
import {Button} from '@wordpress/components';
import getMarkerFromElelement from '../../common/getMarkerFromElelement';

export default function SearchResults({props}) {
	const {
		attributes: {
			markers,
			searchResults,
			mapObj,
			inputRef,
		},
		setAttributes,
	} = props;
	const [searchCount, setSearchCount] = useState(0);
	const addMarker = (e) => {
		const newMarker = getMarkerFromElelement({searchCount, setSearchCount, searchResults}, e);
		setAttributes({
			markers: [
				...markers,
				newMarker
			],
			keywords: '',
			searchResults: [],
		});
		getBounds(props, newMarker, mapObj);
		if (inputRef) {
			inputRef.target.focus();
		}
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
