export default function removeDuplicates(newData, existingData) {

	return newData.filter(dataObj => {
		return !existingData.some(markerObj => JSON.stringify(markerObj) === JSON.stringify(dataObj))
	});
}
