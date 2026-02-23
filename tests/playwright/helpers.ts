// tests/playwright/helpers.ts
import { Page, expect } from '@playwright/test';

const WP_ADMIN = process.env.WP_ADMIN_USER ?? 'admin';
const WP_PASS  = process.env.WP_ADMIN_PASS ?? 'password';

export async function loginIfNeeded( page: Page ) {
  await page.goto( '/wp-admin', { waitUntil: 'domcontentloaded' } );
  const loginForm = page.locator( '#user_login' );
  if ( await loginForm.isVisible( { timeout: 2_000 } ) ) {
    await page.fill( '#user_login', WP_ADMIN );
    await page.fill( '#user_pass', WP_PASS );
    await page.click( '#wp-submit' );
    await page.waitForURL( /wp-admin/ );
  }
  await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
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
  await page.click( 'button[aria-label="Block Inserter"]' );
  await page.fill( '.block-editor-inserter__search input', 'OpenStreetMap' );
  const blockItem = page.locator( '.block-editor-block-types-list__item', {
    hasText: 'OpenStreetMap',
  } ).first();
  await expect( blockItem ).toBeVisible( { timeout: 10_000 } );
  return blockItem;
}
