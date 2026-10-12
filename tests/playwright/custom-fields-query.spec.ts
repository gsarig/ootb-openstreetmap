import { test, expect } from '@playwright/test';
import { loginIfNeeded, dismissModals, editorCanvas } from './helpers';

const VARIATION_TITLE = 'OpenStreetMap from custom fields';

/**
 * Regression test for the custom fields variation querying regular posts.
 *
 * The variation does not set queryArgs, so a freshly inserted block starts with the
 * default `post` type. The test fixture enables exactly one geo post type ('poi'), and
 * the Post type select used to need 2+ options, so it stayed hidden and the block kept
 * querying posts, which can never have locations.
 */
test.describe( 'Custom fields variation with a single geo-enabled post type', () => {

  test( 'defaults to the geo-enabled post type and shows the select', async ({ page }) => {
    await loginIfNeeded( page );
    await dismissModals( page );

    await page.goto( '/wp-admin/post-new.php' );
    const canvas = await editorCanvas( page );
    await expect( canvas.locator( '.is-root-container' ) ).toBeVisible( { timeout: 15_000 } );

    // Dismiss the block editor welcome guide if present
    const editorWelcome = page.locator(
      '.components-modal__header button[aria-label="Close"], .edit-post-welcome-guide .components-button'
    );
    if ( await editorWelcome.count() ) {
      await editorWelcome.first().click().catch( () => {} );
    }

    // Insert the variation itself, not the plain block
    await page.click( 'button[aria-label="Block Inserter"], button[aria-label="Toggle block inserter"]' );
    await page.fill( '.block-editor-inserter__search input', VARIATION_TITLE );
    const variationItem = page.locator( '.block-editor-block-types-list__item', {
      hasText: VARIATION_TITLE,
    } ).first();
    await expect( variationItem ).toBeVisible( { timeout: 10_000 } );
    await variationItem.click();
    await page.keyboard.press( 'Escape' );

    // Open the Settings sidebar if not already open. QueryControl, which applies the
    // default post type, only mounts while the block inspector is shown.
    const settingsButton = page.locator( 'button[aria-label="Settings"]' ).first();
    if ( await settingsButton.isVisible() ) {
      const isPressed = await settingsButton.getAttribute( 'aria-expanded' ) ??
                        await settingsButton.getAttribute( 'aria-pressed' );
      if ( isPressed === 'false' ) {
        await settingsButton.click();
      }
    }

    // The "Map data" panel opens by default for this variation
    const postTypeSelect = page.locator( '.components-select-control', { hasText: 'Post type' } )
      .locator( 'select' );
    await expect( postTypeSelect ).toBeVisible( { timeout: 10_000 } );
    await expect( postTypeSelect.locator( 'option' ) ).toHaveCount( 1 );
    await expect( postTypeSelect ).toHaveValue( 'poi' );

    // The stored attribute, which drives the frontend query, must follow
    await expect.poll( () => page.evaluate(
      () => ( window as any ).wp.data.select( 'core/block-editor' ).getSelectedBlock()?.attributes?.queryArgs?.post_type
    ) ).toBe( 'poi' );

    // The editor notice is where the reporter saw the bug ("Retrieves Posts with locations")
    const notice = canvas.locator( '[data-type="ootb/openstreetmap"] .ootb-custom-fields' );
    await expect( notice ).toHaveText( 'Retrieves Points of Interest with locations' );
  } );
} );
