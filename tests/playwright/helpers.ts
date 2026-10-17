// tests/playwright/helpers.ts
import { FrameLocator, Page, expect } from '@playwright/test';

const WP_ADMIN = process.env.WP_ADMIN_USER ?? 'admin';
const WP_PASS  = process.env.WP_ADMIN_PASS ?? 'password';

// Users created by the setup scripts; each one's login is its role name.
export const ROLES = [ 'admin', 'editor', 'author', 'contributor', 'subscriber' ] as const;
export type Role = ( typeof ROLES )[ number ];

// wp-login.php focuses and selects the username field 200ms after load. If that
// lands while the password is being typed, the password replaces the username and
// the login fails, so fill until both fields hold what was typed.
async function fillLoginForm( page: Page, user: string, pass: string ) {
  await expect( async () => {
    await page.fill( '#user_login', user );
    await page.fill( '#user_pass', pass );
    await expect( page.locator( '#user_login' ) ).toHaveValue( user, { timeout: 1_000 } );
    await expect( page.locator( '#user_pass' ) ).toHaveValue( pass, { timeout: 1_000 } );
  } ).toPass( { timeout: 10_000 } );
}

export async function loginAs( page: Page, role: Role ) {
  await page.goto( '/wp-login.php', { waitUntil: 'domcontentloaded' } );
  await fillLoginForm( page, role, 'password' );
  await page.click( '#wp-submit' );
  await page.waitForURL( /wp-admin/, { timeout: 15_000 } );
  await expect( page.locator( '#wpadminbar' ) ).toBeVisible( { timeout: 15_000 } );
}

// REST nonce for the logged-in user, so page.request calls authenticate by cookie.
export async function restNonce( page: Page ): Promise< string > {
  const response = await page.request.get( '/wp-admin/admin-ajax.php?action=rest-nonce' );
  expect( response.ok() ).toBe( true );
  return ( await response.text() ).trim();
}

export async function loginIfNeeded( page: Page ) {
  await page.goto( '/wp-admin', { waitUntil: 'domcontentloaded' } );

  const loginForm = page.locator( '#user_login' );
  if ( await loginForm.isVisible( { timeout: 10_000 } ) ) {
    await fillLoginForm( page, WP_ADMIN, WP_PASS );
    await page.click( '#wp-submit' );
    await page.waitForURL( /wp-admin/, { timeout: 15_000 } );
  }

  await expect( page.locator( '#wpadminbar' ) ).toBeVisible( { timeout: 15_000 } );
}

// The editor content: the editor-canvas iframe from WordPress 7.0, the page itself
// before that (CI still runs 6.6). Block markup lives here; toolbars and the
// settings sidebar stay on the page. Wait on .is-root-container inside it: the
// iframe has no .block-editor-writing-flow wrapper.
export async function editorCanvas( page: Page ): Promise< Page | FrameLocator > {
  await page.locator( 'iframe[name="editor-canvas"], .block-editor-writing-flow' ).first().waitFor( { timeout: 15_000 } );
  if ( await page.locator( 'iframe[name="editor-canvas"]' ).count() ) {
    return page.frameLocator( 'iframe[name="editor-canvas"]' );
  }
  return page;
}

export async function dismissModals( page: Page ) {
  const dismissSelectors = [
    '.welcome-panel-close',
    '.components-modal__header button[aria-label="Close"]',
    '.notice-dismiss',
  ];
  for ( const selector of dismissSelectors ) {
    const button = page.locator( selector ).first();
    if ( await button.isVisible( { timeout: 500 } ) ) {
      await button.click().catch( () => {} );
    }
  }
}

export async function insertBlock( page: Page ) {
  await page.click( 'button[aria-label="Block Inserter"], button[aria-label="Toggle block inserter"]' );
  await page.fill( '.block-editor-inserter__search input', 'OpenStreetMap' );
  const blockItem = page.locator( '.block-editor-block-types-list__item', {
    hasText: 'OpenStreetMap',
  } ).first();
  await expect( blockItem ).toBeVisible( { timeout: 10_000 } );
  return blockItem;
}
