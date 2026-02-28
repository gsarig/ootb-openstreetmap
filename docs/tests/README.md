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
