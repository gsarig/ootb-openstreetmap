# Running Tests

## Quick start
```bash
composer install
npm ci && npm run build
npx playwright install
make setup        # starts Docker, installs WP, creates test fixtures
make test         # PHPStan + PHPCS + PHPUnit
make playwright   # Playwright smoke test
```

## Updating snapshots

When a block output change is intentional:
```bash
UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --testsuite snapshot
```

Every snapshot update PR must include:
1. Updated fixture files in `tests/phpunit/fixtures/`
2. A one-line changelog entry
3. A rationale in the PR description (reference ARCHITECTURE.md if public output changed)

## Docker environment

WordPress runs at `http://localhost:8080` (admin / password).
Test page: `http://localhost:8080/test-map/`

Reset everything: `docker compose down -v && make setup`

## CI parity

CI runs identical commands. If something passes locally but fails in CI,
reset stale Docker volumes with `docker compose down -v`.

## Known local limitations

### OSM tile 403s in the block editor

When editing a block in the Gutenberg editor on **localhost**, OSM map tiles will not load
and the browser console will show 403 errors. This is expected and is not a code bug.

**Why it happens**: In apiVersion 3, the editor canvas is a `blob:` URL iframe. The
`blob:` origin on localhost is `http://localhost:8080`. OSM's tile servers explicitly
reject requests from localhost origins by policy, regardless of what `Referer` is sent.

**On a production site** tiles load correctly in the editor — the origin sent is the real
domain (e.g. `https://example.com`) which OSM accepts.

**Impact**: none. The block editor on localhost is fully functional — markers, drag, zoom,
and all interactions work. Only the tile background is absent in the editor view.
