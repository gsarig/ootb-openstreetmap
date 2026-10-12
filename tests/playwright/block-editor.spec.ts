import { test, expect, FrameLocator, Page, Request } from '@playwright/test';
import { loginIfNeeded, dismissModals, insertBlock, editorCanvas } from './helpers';

const OSM_TILE_HOST = /(^|\.)tile\.openstreetmap\.org$/;
// 1×1 transparent PNG, served in place of real tiles.
const STUB_TILE_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

test.describe( 'OOTB OpenStreetMap block — editor', () => {

  let canvas: Page | FrameLocator;

  test.beforeEach( async ({ page }) => {
    await loginIfNeeded( page );
    await dismissModals( page );

    await page.goto( '/wp-admin/post-new.php' );
    canvas = await editorCanvas( page );
    await expect( canvas.locator( '.is-root-container' ) ).toBeVisible( { timeout: 15_000 } );

    // Dismiss the block editor welcome guide if present
    const editorWelcome = page.locator(
      '.components-modal__header button[aria-label="Close"], .edit-post-welcome-guide .components-button'
    );
    if ( await editorWelcome.count() ) {
      await editorWelcome.first().click().catch( () => {} );
    }
  } );

  test( 'block appears in the inserter', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await expect( blockItem ).toBeVisible();
  } );

  test( 'block can be inserted and renders a map container in the editor', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    // Fail explicitly if the block validation error is present
    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .block-editor-warning' )
    ).not.toBeVisible();
  } );

  // tile.openstreetmap.org serves an "Access blocked" tile to requests without a
  // Referer, and requests made from the editor's blob: iframe carry none. Assert on
  // the header: the blocked tile is also a 200 response with a valid 256px PNG, so
  // the image alone can't tell the two apart. Without the fix this only fails on
  // WordPress 7.1+, where the editor is always iframed.
  test( 'editor tile requests carry a Referer header', async ({ page }) => {
    const tileRequests: Request[] = [];
    page.on( 'request', request => {
      if ( OSM_TILE_HOST.test( new URL( request.url() ).hostname ) ) {
        tileRequests.push( request );
      }
    } );

    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-tile-loaded' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    expect( tileRequests.length ).toBeGreaterThan( 0 );
    for ( const request of tileRequests ) {
      expect( ( await request.allHeaders() ).referer ).toBeTruthy();
    }
  } );

  // FetchTileLayer shows each fetched tile through a blob: object URL. Unlike the
  // Referer test, this fails on any WordPress version if the editor goes back to
  // plain <img src> tiles.
  test( 'editor tiles load through fetch()', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    const loadedTiles = canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-tile-loaded' );
    await expect( loadedTiles.first() ).toBeVisible( { timeout: 15_000 } );

    const sources = await loadedTiles.evaluateAll( tiles => tiles.map( tile => ( tile as HTMLImageElement ).src ) );
    for ( const src of sources ) {
      expect( src ).toMatch( /^blob:/ );
    }
  } );

  // Providers that don't allow cross-origin fetches must still show tiles, through
  // the plain <img src> fallback. Abort the fetch() outright: a route-fulfilled
  // response without Access-Control-Allow-Origin still let the fetch() succeed in
  // Chromium, so the fallback never ran.
  test( 'editor tiles fall back to a plain image when fetch is refused', async ({ page }) => {
    await page.route( url => OSM_TILE_HOST.test( url.hostname ), route => {
      if ( route.request().resourceType() === 'fetch' ) {
        return route.abort();
      }
      return route.fulfill( {
        contentType: 'image/png',
        body: Buffer.from( STUB_TILE_PNG, 'base64' ),
      } );
    } );

    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    const loadedTiles = canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-tile-loaded' );
    await expect( loadedTiles.first() ).toBeVisible( { timeout: 15_000 } );

    const sources = await loadedTiles.evaluateAll( tiles => tiles.map( tile => ( tile as HTMLImageElement ).src ) );
    for ( const src of sources ) {
      expect( OSM_TILE_HOST.test( new URL( src ).hostname ) ).toBe( true );
    }
  } );

  // In an iframed editor (always from WordPress 7.1) drags used to jump by the
  // iframe's offset and never end on release; see IframeMouseFix.js. Positions are
  // compared in page coordinates; a 2px tolerance absorbs subpixel rounding.
  test( 'map drag follows the mouse and ends on release', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    const map = canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first();
    await expect( map.locator( '.leaflet-tile-loaded' ).first() ).toBeVisible( { timeout: 15_000 } );
    const pane = map.locator( '.leaflet-map-pane' );
    const panePosition = () => pane.evaluate( el => { const r = el.getBoundingClientRect(); return [ r.left, r.top ]; } );

    const box = ( await map.boundingBox() )!;
    const x = box.x + box.width / 2;
    const y = box.y + box.height / 2;
    const start = await panePosition();

    await page.mouse.move( x, y );
    await page.mouse.down();
    await page.mouse.move( x + 10, y + 10, { steps: 2 } );
    const afterFirstMove = await panePosition();
    expect( Math.abs( afterFirstMove[ 0 ] - start[ 0 ] - 10 ) ).toBeLessThanOrEqual( 2 );
    expect( Math.abs( afterFirstMove[ 1 ] - start[ 1 ] - 10 ) ).toBeLessThanOrEqual( 2 );

    await page.mouse.move( x + 150, y + 80, { steps: 10 } );
    await page.mouse.up();
    // Let Leaflet's inertia settle, then moving the mouse must no longer pan the map.
    await page.waitForTimeout( 1_000 );
    const afterRelease = await panePosition();
    await page.mouse.move( x - 100, y - 100, { steps: 10 } );
    expect( await panePosition() ).toEqual( afterRelease );
  } );

  test( 'markers can be added and dragged', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    const map = canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first();
    await expect( map.locator( '.leaflet-tile-loaded' ).first() ).toBeVisible( { timeout: 15_000 } );
    const box = ( await map.boundingBox() )!;

    // A press held for longer than the 300ms pinning delay adds a marker.
    await page.mouse.move( box.x + box.width / 2, box.y + box.height / 2 );
    await page.mouse.down();
    await page.waitForTimeout( 450 );
    await page.mouse.up();
    const marker = map.locator( '.leaflet-marker-icon' ).first();
    await expect( marker ).toBeVisible( { timeout: 5_000 } );
    // Adding a marker re-fits the map to it; wait for that to finish before measuring.
    await page.waitForTimeout( 1_000 );

    const markerBox = ( await marker.boundingBox() )!;
    const x = markerBox.x + markerBox.width / 2;
    const y = markerBox.y + markerBox.height - 4;
    await page.mouse.move( x, y );
    await page.mouse.down();
    await page.mouse.move( x + 80, y + 50, { steps: 10 } );
    const dragged = ( await marker.boundingBox() )!;
    expect( Math.abs( dragged.x - markerBox.x - 80 ) ).toBeLessThanOrEqual( 2 );
    expect( Math.abs( dragged.y - markerBox.y - 50 ) ).toBeLessThanOrEqual( 2 );

    await page.mouse.up();
    await page.waitForTimeout( 300 );
    await page.mouse.move( x - 80, y - 80, { steps: 10 } );
    const afterRelease = ( await marker.boundingBox() )!;
    expect( afterRelease.x ).toBeCloseTo( dragged.x, 0 );
    expect( afterRelease.y ).toBeCloseTo( dragged.y, 0 );
  } );

  test( 'clustering toggle can be enabled without crashing the block', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    // Open the Settings sidebar if not already open
    const settingsButton = page.locator( 'button[aria-label="Settings"]' ).first();
    if ( await settingsButton.isVisible() ) {
      const isPressed = await settingsButton.getAttribute( 'aria-expanded' ) ??
                        await settingsButton.getAttribute( 'aria-pressed' );
      if ( isPressed === 'false' ) {
        await settingsButton.click();
      }
    }

    // Expand the "Map behavior" panel (it's closed by default)
    const behaviorPanel = page.locator( '.components-panel__body-toggle', { hasText: 'Map behavior' } );
    await expect( behaviorPanel ).toBeVisible( { timeout: 5_000 } );
    const isOpen = await behaviorPanel.evaluate( el => el.closest( '.components-panel__body' )?.classList.contains( 'is-opened' ) );
    if ( ! isOpen ) {
      await behaviorPanel.click();
    }

    // Collect JS errors before enabling clustering
    const jsErrors: string[] = [];
    page.on( 'pageerror', err => jsErrors.push( err.message ) );

    // Enable the Cluster markers toggle
    const clusterToggle = page.locator( '.components-toggle-control', { hasText: 'Cluster markers' } )
      .locator( 'input[type="checkbox"]' );
    await expect( clusterToggle ).toBeVisible( { timeout: 5_000 } );
    await clusterToggle.click();

    // Map container must still be visible (no block crash)
    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 10_000 } );

    // Block validation error must not appear
    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .block-editor-warning' )
    ).not.toBeVisible();

    // No uncaught JS errors triggered by enabling the toggle
    expect( jsErrors ).toHaveLength( 0 );
  } );

  test( 'fullscreen control appears in the editor when the toggle is enabled', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    // Open the Settings sidebar if not already open
    const settingsButton = page.locator( 'button[aria-label="Settings"]' ).first();
    if ( await settingsButton.isVisible() ) {
      const isPressed = await settingsButton.getAttribute( 'aria-expanded' ) ??
                        await settingsButton.getAttribute( 'aria-pressed' );
      if ( isPressed === 'false' ) {
        await settingsButton.click();
      }
    }

    // Expand the "Map behavior" panel (it's closed by default)
    const behaviorPanel = page.locator( '.components-panel__body-toggle', { hasText: 'Map behavior' } );
    await expect( behaviorPanel ).toBeVisible( { timeout: 5_000 } );
    const isOpen = await behaviorPanel.evaluate( el => el.closest( '.components-panel__body' )?.classList.contains( 'is-opened' ) );
    if ( ! isOpen ) {
      await behaviorPanel.click();
    }

    // Enable the Fullscreen mode toggle
    const fullscreenToggle = page.locator( '.components-toggle-control', { hasText: 'Fullscreen mode' } )
      .locator( 'input[type="checkbox"]' );
    await expect( fullscreenToggle ).toBeVisible( { timeout: 5_000 } );
    await fullscreenToggle.click();

    // The fullscreen control button should now appear in the editor map
    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-control-fullscreen' )
    ).toBeVisible( { timeout: 5_000 } );
  } );

} );
