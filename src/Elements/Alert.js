// noinspection NpmUsedModulesInstalled
import {__} from '@wordpress/i18n';

export default function Alert({props}) {
	const {
		attributes: {
			addingMarker,
			openAImode,
		},
	} = props;
	let alert = '';
	if (' pinning' === addingMarker) {
		alert = __('Release to drop a marker here', 'ootb-openstreetmap');
	} else if (openAImode.length) {
		if ('started' === openAImode) {
			alert = __('You have entered OpenAI mode. When you finish your prompt, please hit enter or click on the bubble icon.', 'ootb-openstreetmap');
		} else if ('connecting' === openAImode) {
			alert = __('Connecting to OpenAI...', 'ootb-openstreetmap');
		} else if ('success' === openAImode) {
			alert = __('Preparing the data. Please be patient...', 'ootb-openstreetmap');
		} else if ('working' === openAImode) {
			alert = __('Creating the markers. It will take a while, because we don\'t want to hit the Nominatim rate limits.', 'ootb-openstreetmap');
		} else if ('invalid_question' === openAImode) {
			alert = __('I don\'t understand your question. Please try again.', 'ootb-openstreetmap');
		} else if ('error' === openAImode) {
			alert = __('Something went wrong. Sorry...', 'ootb-openstreetmap');
		}
	}

	return alert ?
		<div className="ootb-openstreetmap--alert">{alert}</div>
		: null;
}
