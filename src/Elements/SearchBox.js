// noinspection NpmUsedModulesInstalled

import SearchResults from './SearchResults';
import {__} from '@wordpress/i18n';
import {TextControl, Button} from '@wordpress/components';
import {Fragment} from '@wordpress/element';
import {openaiAnswers} from '../Helpers/openai';
import getNominatimSearchUrl from "../Helpers/getNominatimSearchUrl";


export default function SearchBox({props}) {
	const {
		attributes: {
			keywords,
			inputRef,
			searchResults,
			openAImode,
		},
		setAttributes,
	} = props;
	const findMarkers = () => {
		if (keywords && keywords.length > 2) {
			fetch(getNominatimSearchUrl(keywords))
				.then(response => {
					if (200 !== response.status) {
						return;
					}
					return response.json();
				}).then(data => {
				setAttributes({
					searchResults: data,
				});
			});
		}
	}

	const onTyping = (text) => {
		const regex = /\bplease\b/i;
		setAttributes({
			keywords: text,
			openAImode: regex.test(text) ? 'started' : '',
		});
	}

	const detectEnter = (e) => {
		if (!inputRef) {
			setAttributes({inputRef: e});
		}
		if ('Enter' === e.key) {
			openaiAnswers(props).then(() => null);
			findMarkers();
		}
	}

	const onButtonClick = () => {
		openaiAnswers(props).then(() => null);
		if (searchResults && searchResults.length) {
			if (inputRef) {
				inputRef.target.focus();
				setAttributes({
					searchResults: [],
					keywords: '',
				});
			}
		} else {
			findMarkers();
		}
	}

	const icon = () => {
		if (searchResults && searchResults.length > 0) {
			return 'no';
		} else if (openAImode.length) {
			return 'format-status';
		} else {
			return 'search';
		}
	}

	let searchBoxClass = "ootb-openstreetmap--searchbox";
	if (openAImode.length) {
		searchBoxClass += " openai-active";
	}
	return (
		<Fragment>
			<div className={searchBoxClass}>
				<TextControl
					value={keywords}
					onChange={onTyping}
					onKeyDown={detectEnter}
					placeholder={__('Enter your keywords...', 'ootb-openstreetmap')}
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
			<SearchResults props={props}/>
		</Fragment>
	);
}
