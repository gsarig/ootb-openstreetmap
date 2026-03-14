import { test, expect } from '@playwright/test';
import { loginIfNeeded, dismissModals, insertBlock } from './helpers';

test.describe( 'OOTB OpenStreetMap block — editor', () => {

  test.beforeEach( async ({ page }) => {
    await loginIfNeeded( page );
    await dismissModals( page );

    await page.goto( '/wp-admin/post-new.php' );
    await expect( page.locator( '.block-editor-writing-flow' ) ).toBeVisible( { timeout: 15_000 } );

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
      page.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    // Fail explicitly if the block validation error is present
    await expect(
      page.locator( '[data-type="ootb/openstreetmap"] .block-editor-warning' )
    ).not.toBeVisible();
  } );

  test( 'clustering toggle can be enabled without crashing the block', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      page.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
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
      page.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 10_000 } );

    // Block validation error must not appear
    await expect(
      page.locator( '[data-type="ootb/openstreetmap"] .block-editor-warning' )
    ).not.toBeVisible();

    // No uncaught JS errors triggered by enabling the toggle
    expect( jsErrors ).toHaveLength( 0 );
  } );

  test( 'fullscreen control appears in the editor when the toggle is enabled', async ({ page }) => {
    const blockItem = await insertBlock( page );
    await blockItem.click();
    await page.keyboard.press( 'Escape' );

    await expect(
      page.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
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
      page.locator( '[data-type="ootb/openstreetmap"] .leaflet-control-fullscreen' )
    ).toBeVisible( { timeout: 5_000 } );
  } );

} );
