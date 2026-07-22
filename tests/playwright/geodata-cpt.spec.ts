import { test, expect } from '@playwright/test';
import { loginIfNeeded, dismissModals } from './helpers';

/**
 * Regression test for the geodata custom fields panel crash (PR #154).
 *
 * The 'poi' CPT (registered by docker/mu-plugins/poi-cpt-fixture.php) has no
 * 'custom-fields' support, so getEditedPostAttribute('meta') returns undefined
 * in its editor. Before the guard, rendering the Location panel crashed the
 * whole editor with "Cannot read properties of undefined (reading 'geo_address')".
 */
test.describe( 'Geodata panel on a CPT without custom-fields support', () => {

  test( 'Location panel renders without crashing the editor', async ({ page }) => {
    await loginIfNeeded( page );
    await dismissModals( page );

    // Collect uncaught JS errors from the very start: the panel may be open
    // on load, which is exactly how the original crash presented.
    const jsErrors: string[] = [];
    page.on( 'pageerror', err => jsErrors.push( err.message ) );

    await page.goto( '/wp-admin/post-new.php?post_type=poi' );
    await expect( page.locator( '.block-editor-writing-flow' ) ).toBeVisible( { timeout: 15_000 } );

    // Dismiss the block editor welcome guide if present
    const editorWelcome = page.locator(
      '.components-modal__header button[aria-label="Close"], .edit-post-welcome-guide .components-button'
    );
    if ( await editorWelcome.count() ) {
      await editorWelcome.first().click().catch( () => {} );
    }

    // Expand the Location panel if it is collapsed (its open state persists per user)
    const locationPanel = page.locator( '.components-panel__body-toggle', { hasText: 'Location' } );
    await expect( locationPanel ).toBeVisible( { timeout: 10_000 } );
    const isOpen = await locationPanel.evaluate(
      el => el.closest( '.components-panel__body' )?.classList.contains( 'is-opened' )
    );
    if ( ! isOpen ) {
      await locationPanel.click();
    }

    // The panel content must render with its map
    await expect(
      page.locator( '.ootb-openstreetmap--custom-fields-container .leaflet-container' )
    ).toBeVisible( { timeout: 15_000 } );

    // The editor must not have crashed into its error boundary
    await expect( page.locator( '.editor-error-boundary' ) ).toHaveCount( 0 );

    // No uncaught JS errors (the pre-fix crash surfaced here as a TypeError on 'geo_address')
    expect( jsErrors ).toHaveLength( 0 );

    // The untouched post must not be marked dirty by a phantom meta edit
    // (setMetaValues no-ops when meta is not exposed over REST)
    const isDirty = await page.evaluate(
      () => ( window as any ).wp.data.select( 'core/editor' ).isEditedPostDirty()
    );
    expect( isDirty ).toBe( false );
  } );

} );
