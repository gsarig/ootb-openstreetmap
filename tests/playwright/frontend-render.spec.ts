import { test, expect } from '@playwright/test';

const MAPBOX_TOKEN = process.env.MAPBOX_ACCESS_TOKEN ?? '';

test.describe('OOTB OpenStreetMap block — smoke test', () => {
  test('map container and marker render on the test page', async ({ page }) => {
    await page.goto('/test-map/');
    await expect(page).not.toHaveTitle(/Error|404|Not Found/i);

    const mapContainer = page.locator('.leaflet-container').first();
    await expect(mapContainer).toBeVisible({ timeout: 15_000 });

    const markers = page.locator('.leaflet-marker-icon');
    await expect(markers).toHaveCount(1, { timeout: 10_000 });

    await page.screenshot({ path: 'tests/playwright/screenshots/smoke-map.png', fullPage: false });
  });

  test('cluster badges appear on the clustering test page', async ({ page }) => {
    await page.goto('/test-map-cluster/');
    await expect(page).not.toHaveTitle(/Error|404|Not Found/i);

    const mapContainer = page.locator('.leaflet-container').first();
    await expect(mapContainer).toBeVisible({ timeout: 15_000 });

    // Leaflet.markercluster renders cluster badges with class marker-cluster
    // (inside a leaflet-marker-icon wrapper)
    const clusterBadge = page.locator('.marker-cluster').first();
    await expect(clusterBadge).toBeVisible({ timeout: 10_000 });

    await page.screenshot({ path: 'tests/playwright/screenshots/cluster-map.png', fullPage: false });
  });

  test('Mapbox tiles are requested when provider is mapbox', async ({ page }) => {
    test.skip( ! MAPBOX_TOKEN, 'MAPBOX_ACCESS_TOKEN not set — skipping Mapbox tile test' );

    const tileRequest = page.waitForRequest(
      ( req ) => {
        try {
          const url = new URL( req.url() );
          return url.hostname === 'api.mapbox.com';
        } catch {
          return false;
        }
      },
      { timeout: 15_000 }
    );

    await page.goto( '/test-map-mapbox/' );
    await expect( page ).not.toHaveTitle( /Error|404|Not Found/i );

    const mapContainer = page.locator( '.leaflet-container' ).first();
    await expect( mapContainer ).toBeVisible( { timeout: 15_000 } );

    await tileRequest;

    await page.screenshot( { path: 'tests/playwright/screenshots/mapbox-map.png', fullPage: false } );
  } );
});
