import SearchResults from './SearchResults';
import {__} from '@wordpress/i18n';
import {TextControl, Button} from '@wordpress/components';
import {Fragment} from '@wordpress/element';

export default function SearchBox({props}) {
	const {
		attributes: {
			keywords,
			inputRef,
			searchResults,
		},
		setAttributes,
	} = props;
	const findMarkers = () => {
		if (keywords && keywords.length > 2) {
			fetch('https://nominatim.openstreetmap.org/search?q=' + keywords + '&format=json')
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
		setAttributes({
			keywords: text,
		});
	}

	const detectEnter = (e) => {
		if (!inputRef) {
			setAttributes({inputRef: e});
		}
		if ('Enter' === e.key) {
			findMarkers();
		}
	}

	const onButtonClick = () => {
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
	// noinspection JSXNamespaceValidation
	return (
		<Fragment>
			<div className="ootb-openstreetmap--searchbox">
				<TextControl
					value={keywords}
					onChange={onTyping}
					onKeyDown={detectEnter}
					placeholder={__('Enter your keywords...', 'ootb-openstreetmap')}
				/>
				<Button
					onClick={onButtonClick}
					icon={(searchResults && searchResults.length > 0) ? 'no' : 'search'}
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
