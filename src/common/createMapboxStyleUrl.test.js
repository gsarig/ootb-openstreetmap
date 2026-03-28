import createMapboxStyleUrl from './createMapboxStyleUrl';

const FAKE_TOKEN = 'pk.fakeToken123';
const VALID_STYLE_URL = 'mapbox://styles/testuser/abc123';
const EXPECTED_URL = 'https://api.mapbox.com/styles/v1/testuser/abc123/tiles/{z}/{x}/{y}?access_token=pk.fakeToken123';

describe( 'createMapboxStyleUrl', () => {
	it( 'converts a valid style URL and token into a Leaflet tile URL', () => {
		expect( createMapboxStyleUrl( VALID_STYLE_URL, FAKE_TOKEN ) ).toBe( EXPECTED_URL );
	} );

	it( 'returns empty string when styleUrl is missing', () => {
		expect( createMapboxStyleUrl( '', FAKE_TOKEN ) ).toBe( '' );
	} );

	it( 'returns empty string when accessToken is missing', () => {
		expect( createMapboxStyleUrl( VALID_STYLE_URL, '' ) ).toBe( '' );
	} );

	it( 'returns empty string when styleUrl does not start with mapbox://', () => {
		expect( createMapboxStyleUrl( 'https://api.mapbox.com/styles/v1/testuser/abc123', FAKE_TOKEN ) ).toBe( '' );
	} );

	it( 'returns empty string when styleUrl is missing the style ID', () => {
		expect( createMapboxStyleUrl( 'mapbox://styles/testuser', FAKE_TOKEN ) ).toBe( '' );
	} );

	it( 'returns empty string when either argument is not a string', () => {
		expect( createMapboxStyleUrl( null, FAKE_TOKEN ) ).toBe( '' );
		expect( createMapboxStyleUrl( VALID_STYLE_URL, null ) ).toBe( '' );
	} );
} );
