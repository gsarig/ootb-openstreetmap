import { defineConfig } from 'vitest/config';

// Unit tests live next to the source. tests/playwright holds the Playwright
// specs, which Vitest would otherwise pick up and fail to run.
export default defineConfig( {
	test: {
		include: [ 'src/**/*.test.js' ],
	},
} );
