// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import fitBounds from './fitBounds';
import getNominatimSearchUrl from '../../common/getNominatimSearchUrl';

export async function openaiAnswers(props) {
	const {
		attributes: {
			markers,
			keywords,
			mapObj,
			openAImode,
		},
		setAttributes
	} = props;
	if (!keywords) {
		return;
	}
	if (!openAImode) {
		return;
	}
	let newMarkers = [...markers];

	const addMarker = (data) => {
		const newMarker = {
			lat: data?.lat.toString() ?? '',
			lng: data?.lon.toString() ?? '',
			text: data?.display_name ? `<p>${data.display_name}</p>` : '',
			id: Date.now(),
		};
		newMarkers.push(newMarker);
		let boundsArr = [];

		if (newMarkers.length > 0) {
			newMarkers.forEach((value) => {
				if (value) {
					boundsArr.push([value.lat, value.lng]);
				}
			});
			setAttributes({
				bounds: boundsArr,
				markers: newMarkers,
				openAImode: 'working',
			});
			fitBounds(boundsArr, mapObj);
		}
	}

	const findMarkers = (place) => {
		if (place && place.length > 2) {
			fetch(getNominatimSearchUrl(place, 1))
				.then(response => {
					if (!response.ok) {
						throw new Error(`Network response was not ok, status: ${response.status}`);
					}
					return response.json();
				})
				.then(data => {
					addMarker(data[0]);
				})
				.catch(error => {
					setAttributes({
						openAImode: 'error',
					});
					setTimeout(() => {
						setAttributes({
							openAImode: '',
							keywords: '',
						});
					}, 3000); // Remove the error message after 3 seconds.
					console.error('There has been a problem with the fetch operation:', JSON.stringify(error));
				});
		}
	}
	setAttributes({
		openAImode: 'connecting',
	});
	return wp.apiFetch({
		path: '/ootb-openstreetmap/v1/openai/',
		method: 'POST',
		data: {prompt: keywords}
	})
		.then(result => {
			let results = [];
			try {
				const resultsRaw = result?.choices[0]?.message?.content;
				if (!resultsRaw || 'invalid_question' === resultsRaw) {
					setAttributes({
						openAImode: 'invalid_question',
					});
					setTimeout(() => {
						setAttributes({
							openAImode: '',
							keywords: '',
						});
					}, 3000); // Remove the error message after 3 seconds.
				} else {
					results = JSON.parse(resultsRaw);
					setAttributes({
						openAImode: 'success',
					});
				}
			} catch (e) {
				setAttributes({
					openAImode: 'error',
				});
				console.error('Error parsing AI response');
			}
			let tasks = results?.map((place, index) =>
				new Promise(resolve => setTimeout(() => {
					findMarkers(place);
					resolve();
				}, 1000 * (index + 1))) // Delay to avoid rate limiting.
			);

			Promise.all(tasks).then(() => {
				setTimeout(() => {
					setAttributes({
						openAImode: '',
						keywords: '',
					});
				}, 1000); // Wait for the last marker to be added.
			});
		})
		.catch(error => {
			setAttributes({
				openAImode: 'error',
			});
			setTimeout(() => {
				setAttributes({
					openAImode: '',
					keywords: '',
				});
			}, 3000); // Remove the error message after 3 seconds.
			console.error('Error fetching AI response: ' + JSON.stringify(error));
		});
}
