// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable
import validMarkers from '../Helpers/validMarkers';

const queryMarkers = (props, postId) => {
	const {
		attributes: {
			queryArgs,
		},
		setAttributes,
	} = props;
	const formData = new URLSearchParams({
		action: 'ootb_get_markers',
		post_id: postId,
		query_args: JSON.stringify(queryArgs),
		nonce: ootbGlobal.nonce,
	});
	fetch(ootbGlobal.ajaxUrl, {
		method: 'POST',
		body: formData,
	})
		.then(response => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then(response => {
			const queriedMarkers = validMarkers(response?.data);
			if (!queriedMarkers) {
				return;
			}
			setAttributes({
				markers: queriedMarkers,
				shouldUpdateBounds: true,
				serverSideRender: false,
			});
		})
		.catch((error) => {
			console.error('Fetch Error:', error);
		});
}
export {queryMarkers};
