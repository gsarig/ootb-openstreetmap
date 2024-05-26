export default function getNominatimSearchUrl(keyword, limit = 0) {
	const limitParam = (limit > 0) ? '&limit=' + limit : '';
	return 'https://nominatim.openstreetmap.org/search?q=' + keyword + limitParam + '&format=json';
}
