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

} );
