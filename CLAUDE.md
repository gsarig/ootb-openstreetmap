# OOTB OpenStreetMap — WordPress Plugin

## Project Overview
A Gutenberg block plugin for WordPress that renders interactive OpenStreetMap maps using Leaflet.js.
No API keys required. Features: drag-and-drop markers, WYSIWYG popup editing, place search, polygons,
polylines, Query Maps, shortcode support, AI-assisted marker placement, and geodata custom field support.

Published on WordPress.org: https://wordpress.org/plugins/ootb-openstreetmap/

---

## Critical Development Rules

### Minimize Impact
Make the smallest possible change to achieve the goal. Do not refactor, rename, or restructure
code that is not directly related to the task at hand. Working code must stay working.
Never introduce regressions in the name of improvement unless explicitly asked.

### Minimize File Changes
Touch as few files as possible per task. If a new feature can live in its own dedicated file,
create a new file rather than expanding an existing one. Each file should own a specific concern —
changes must be isolated so the rest of the codebase remains untouched.

### PHP vs. JS Boundary
This plugin has two distinct layers: PHP (block registration, rendering, REST, settings) and
JavaScript (Gutenberg editor, Leaflet map frontend). If a requested change affects both layers,
**stop and warn before proceeding**. Most tasks are scoped to one or the other. Cross-layer
impact is unexpected and needs explicit sign-off.

### Public API Stability
This plugin is published on WordPress.org and has real users. Any change to hooks
(ootb_query_post_type, ootb_query_posts_per_page, ootb_query_extra_args,
ootb_cf_modal_content, ootb_block_marker_text, ootb_cf_marker_icon),
shortcode attributes, or block attribute names is a **breaking change**.
Flag it clearly before implementing and confirm it is intentional.

### Distribution Zip Is the Real Artefact
The plugin is deployed from a zip built via the artifact pipeline, not directly from GitHub.
Before any release merge to master, the `artifact-playwright` CI job must pass. Never treat
green source tests as confirmation the distribution zip is correct — they are tested separately.
The artifact job builds the zip with `bin/build-zip.sh` (identical to what `release.yml` deploys),
installs it into a clean WordPress instance via WP-CLI, and runs the full Playwright suite against it.

### Snapshot Tests Are the Rendered Output Contract
PHPUnit snapshot tests in tests/phpunit/snapshot/ define the expected HTML output of the block.
If an implementation changes the rendered HTML in any way, the snapshots will fail.
This is intentional — do NOT auto-update snapshots unless explicitly asked.
When snapshots fail due to an intentional change, say so, explain what changed and why,
and wait for explicit confirmation before running make update-snapshots.

---

## Architecture Notes

### render_callback runs for all block renders
`Query::render_callback` (`includes/core.php`) is registered as the `render_callback` for
**every** block render, not just shortcodes. It runs the WP_Query path unless the
`server_side_render` attribute is explicitly set to `false`.

**Implication for test pages**: any page whose block content relies on its static saved HTML
(e.g. Playwright test fixture pages) must include `"serverSideRender":false` in the block
comment JSON, or the render callback will replace the static markers with a WP_Query result.

### Script handles are registered on enqueue_block_assets
All plugin script handles (e.g. `ootb-openstreetmap-view-script`) are registered inside
`Assets::frontend_assets()`, which runs on `enqueue_block_assets`.

**Implication for examples**: any code example that calls `wp_add_inline_script` targeting
a plugin handle must hook into `enqueue_block_assets`, not `wp_enqueue_scripts` — otherwise
the handle doesn't exist yet and the call is silently ignored.

---

## Stack

- **PHP** 8.1+ — block rendering, REST endpoints, settings, shortcodes
- **JavaScript** (ES modules) — Gutenberg block editor (React), Leaflet.js frontend
- **Leaflet.js** — map rendering library
- **WordPress** 6.6+ with Gutenberg block API
- **Composer** — PHP dependencies and dev tools
- **npm** — JS build pipeline

---

## Project Structure

```
ootb-openstreetmap/
├── ootb-openstreetmap.php      # Plugin entry point
├── src/
│   ├── Block/                  # Block registration and server-side render
│   ├── Api/                    # REST API endpoints
│   ├── QueryMap/               # Query Maps feature (fetch markers from posts)
│   ├── Shortcode/              # [ootb_query] shortcode
│   ├── Settings/               # Plugin settings page
│   └── ...
├── assets/
│   ├── js/                     # Frontend JS (Leaflet map)
│   └── build/                  # Compiled assets (do not edit directly)
├── includes/                   # Shared PHP utilities
├── tests/
│   ├── phpunit/
│   │   ├── bootstrap.php
│   │   ├── fixtures/           # Snapshot files (auto-generated, commit carefully)
│   │   └── snapshot/
│   │       └── BlockSnapshotTest.php
│   └── playwright/
│       ├── frontend-render.spec.ts
│       └── screenshots/        # Gitignored
├── composer.json
├── phpstan.neon                # PHPStan config (level 6)
├── phpcs.xml                   # WordPress Coding Standards config
├── phpunit.xml.dist
├── playwright.config.ts
├── Makefile                    # All standard commands
└── docker-compose.yml          # Local test environment
```

---

## Commands

### Setup (first time or after reset)
```bash
composer install
npm ci && npm run build
npx playwright install
make setup          # Starts Docker, installs WordPress, creates test fixtures
```

### Development
```bash
make up             # Start Docker containers
make down           # Stop containers (keeps data)
npm run build       # Rebuild JS assets after JS changes
```

### Testing — Run After Every Change
Always run tests after implementing anything. All must pass before a task is done.

**Run in this order (fail fast):**
```bash
make lint           # PHPStan (level 6) + PHPCS — fastest, run first
make phpunit        # PHPUnit snapshot tests — requires Docker running
make playwright     # Playwright E2E smoke tests — slowest, run last
```

Or run everything at once:
```bash
make test           # lint + phpunit
make playwright     # separate due to Docker dependency
```

### Single test / debug
```bash
vendor/bin/phpunit --filter=test_name   # Run one PHPUnit test
npx playwright test --headed            # With browser UI
npx playwright test --debug             # Debug mode
```

### Updating Snapshots (only when explicitly asked)
```bash
make update-snapshots
# or:
UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --testsuite snapshot
```
After updating, always review git diff tests/phpunit/fixtures/ before committing.
Every snapshot update requires: updated fixture files + changelog entry + rationale.

---

## Testing Workflow

This is the expected workflow for every implementation task:

1. Implement the requested change
2. Run make lint — fix any PHPStan or PHPCS errors before continuing
3. Run make phpunit — if snapshots fail due to an **unintentional** output change, treat it as a regression and fix it; if the change is **intentional**, stop and report before updating
4. Run make playwright — fix any E2E failures
5. Only report the task as done when all three pass

If tests are still failing after 3 fix attempts, **stop and explain** what was tried and
why it is still failing — do not keep iterating blindly.

---

## Docker Environment

WordPress runs at http://localhost:8080
- Admin: admin / password
- Test page: http://localhost:8080/test-map/

Reset everything: docker compose down -v && make setup

---

## Environment Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| WP_TESTS_DIR | /tmp/wordpress-tests-lib | PHPUnit test suite location |
| WP_BASE_URL | http://localhost:8080 | WordPress URL for Playwright |
| UPDATE_SNAPSHOTS | (unset) | Set to 1 to update snapshots |

---

## Code Style

- Follow **WordPress Coding Standards** (enforced by PHPCS via phpcs.xml)
- **PHPStan level 6** — no errors allowed (configured in phpstan.neon)
- PHP 8.1+ — use typed properties, union types, named arguments where appropriate
- JavaScript: ES modules, async/await
- No magic numbers or hardcoded strings — use constants or configuration
- Escape all output; sanitize and validate all input (WordPress security standards)

---

## Public Hooks Reference

These are part of the public API — do not change signatures or remove without flagging:

| Hook | Type | Purpose |
|------|------|---------|
| ootb_query_post_type | filter | Change queried post type(s) |
| ootb_query_posts_per_page | filter | Change posts per page |
| ootb_query_extra_args | filter | Add extra WP_Query args |
| ootb_cf_modal_content | filter | Customize geodata marker popup content |
| ootb_block_marker_text | filter | Customize block marker popup content |
| ootb_cf_marker_icon | filter | Customize geodata marker icon |

---

## Key Features for Context

- **Block**: ootb/openstreetmap — main Gutenberg block
- **Shortcode**: [ootb_query] — dynamic map from queried posts
- **Query Maps**: fetches markers from other posts/post types dynamically
- **Geodata**: stores location in post meta following WordPress Geodata spec
- **AI integration**: natural language marker placement (requires external API key)
- **Layer providers**: OpenStreetMap (default), MapBox, Stamen
- **Map types**: markers, polygon, polyline

---

## Release Procedure

When asked to **"deploy the release"**, execute the following steps in order — do not skip any, do not push anything.

### Steps (automated)

1. **Confirm the target version** — ask if not told explicitly.
2. **Update all four version strings** to the new version:
   - ` * Version:` header in `ootb-openstreetmap.php`
   - `OOTB_VERSION` constant in `ootb-openstreetmap.php`
   - `"version"` in `package.json`
   - `"version"` in `src/block/block.json`
   - `Stable tag:` in `readme.txt`
3. **Add a changelog entry** at the top of the `== Changelog ==` section in `readme.txt`:
   ```
   = X.Y.Z =
   * ...user-facing changes...
   ```
4. **Commit** all changed files with message: `Bump version to X.Y.Z and update changelog`
5. **Create a git tag**: `git tag X.Y.Z`

### Steps (manual — you do these)

After the above is done, report:

> Done. Now run:
> ```
> git push origin --tags
> git push origin master
> ```

### Version consistency rule
All five locations must match exactly before tagging. The `artifact-playwright` CI job checks three of them (PHP header, `package.json`, `readme.txt`) and fails fast if they diverge.

---

## CI Pipeline (GitHub Actions)

```
static-analysis + js-build + security + artifact-playwright (parallel)
                       |                        |
               phpunit + playwright         builds its own zip
             (wait for js-build)         installs to clean WP
                                         runs Playwright on zip
```

- `artifact-playwright` runs independently — it does not reuse `js-build` assets.
  It builds the full distribution zip from scratch to test exactly what gets deployed.
- Version consistency check (PHP header / package.json / readme.txt) runs first in the
  artifact job and fails fast if versions are out of sync.

CI runs identical commands to local. If something passes locally but fails in CI,
reset stale Docker volumes: docker compose down -v && make setup
