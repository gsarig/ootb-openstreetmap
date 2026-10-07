/**
 * Role boundaries: one allowed and one refused case for every capability check in OOTB.
 * A new current_user_can() call in includes/ needs a row here.
 *
 * - edit_posts, OpenAI location search (POST ootb-openstreetmap/v1/openai): admin, editor,
 *   author and contributor pass the permission check, and the callback then answers 400
 *   because the test site has no AI backend. A subscriber gets 403 and a logged-out visitor
 *   401 (core's rest_forbidden).
 * - edit_posts and edit_post, the ootb-openstreetmap/add-map-to-post ability: it is not
 *   exposed over REST, so tests/phpunit/integration/AbilitiesTest.php covers both through
 *   core's Abilities API. Those tests skip on WordPress 6.6, which has no Abilities API.
 * - manage_options (Settings > OOTB OpenStreetMap): admin allowed, every other role 403.
 * - The front end: a logged-out visitor and a subscriber see the test page's map.
 * - The block editor: editor, author and contributor insert the block and save a draft.
 */
import { test, expect, Page } from '@playwright/test';
import { ROLES, Role, loginAs, restNonce, insertBlock, editorCanvas } from './helpers';

const OPENAI_ROUTE = '/?rest_route=/ootb-openstreetmap/v1/openai/';

async function searchLocation( page: Page, nonce?: string ) {
  return page.request.post( OPENAI_ROUTE, {
    headers: nonce ? { 'X-WP-Nonce': nonce } : {},
    data: { prompt: 'Athens' },
  } );
}

for ( const role of ROLES ) {
  const canEdit = role !== 'subscriber';
  const canManage = role === 'admin';

  test( `[${ role }] OpenAI search ${ canEdit ? 'passes the permission check (400, no AI backend)' : 'is refused with 403' }`, async ( { page } ) => {
    await loginAs( page, role );
    const response = await searchLocation( page, await restNonce( page ) );
    expect( response.status() ).toBe( canEdit ? 400 : 403 );
  } );

  test( `[${ role }] Settings > OOTB OpenStreetMap is ${ canManage ? 'shown' : 'refused' }`, async ( { page } ) => {
    await loginAs( page, role );
    const response = await page.goto( '/wp-admin/options-general.php?page=ootb-openstreetmap' );
    if ( canManage ) {
      await expect( page.getByRole( 'heading', { level: 1, name: 'Out of the Block: OpenStreetMap' } ) ).toBeVisible();
    } else {
      expect( response?.status() ).toBe( 403 );
    }
  } );
}

test( '[visitor] OpenAI search is refused with 401', async ( { page } ) => {
  expect( ( await searchLocation( page ) ).status() ).toBe( 401 );
} );

for ( const role of [ 'visitor', 'subscriber' ] as const ) {
  test( `[${ role }] sees the map on the test page`, async ( { page } ) => {
    if ( role === 'subscriber' ) {
      await loginAs( page, role );
    }
    await page.goto( '/test-map/' );
    await expect( page.locator( '.leaflet-container' ).first() ).toBeVisible( { timeout: 15_000 } );
    await expect( page.locator( '.leaflet-marker-icon' ) ).toHaveCount( 1, { timeout: 10_000 } );
  } );
}

for ( const role of [ 'editor', 'author', 'contributor' ] as Role[] ) {
  test( `[${ role }] inserts the block and saves a draft`, async ( { page } ) => {
    await loginAs( page, role );
    await page.goto( '/wp-admin/post-new.php' );
    const canvas = await editorCanvas( page );
    await expect( canvas.locator( '.is-root-container' ) ).toBeVisible( { timeout: 15_000 } );
    await page.evaluate( () => {
      ( window as any ).wp.data.dispatch( 'core/preferences' ).set( 'core/edit-post', 'welcomeGuide', false );
    } );

    await ( await insertBlock( page ) ).click();
    await page.keyboard.press( 'Escape' );
    await expect(
      canvas.locator( '[data-type="ootb/openstreetmap"] .leaflet-container' ).first()
    ).toBeVisible( { timeout: 15_000 } );

    await page.getByRole( 'button', { name: 'Save draft' } ).click();
    await page.waitForFunction( () => {
      const editor = ( window as any ).wp.data.select( 'core/editor' );
      return ! editor.isSavingPost() && ! editor.isEditedPostDirty();
    } );

    const postId = await page.evaluate( () => ( window as any ).wp.data.select( 'core/editor' ).getCurrentPostId() );
    const saved = await page.request.get( `/?rest_route=/wp/v2/posts/${ postId }&context=edit`, {
      headers: { 'X-WP-Nonce': await restNonce( page ) },
    } );
    expect( saved.ok() ).toBe( true );
    const post = await saved.json();
    expect( post.status ).toBe( 'draft' );
    expect( post.content.raw ).toContain( '<!-- wp:ootb/openstreetmap' );
  } );
}
