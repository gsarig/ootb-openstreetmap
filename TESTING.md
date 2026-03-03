# Testing Infrastructure Guide

Complete CI and testing setup for the OOTB OpenStreetMap WordPress plugin.

## Quick Start

```bash
# Install dependencies
composer install
npm ci && npm run build
npx playwright install

# Start Docker environment and setup WordPress
make setup

# Run all tests
make test

# Run specific test suites
make lint          # PHPStan + PHPCS only
make phpunit       # PHPUnit snapshot tests
make playwright    # Browser smoke tests
```

## Architecture

### Test Suites

1. **Static Analysis (PHPStan)**
   - Level 6 analysis of PHP code
   - WordPress-specific rules via `szepeviktor/phpstan-wordpress`
   - Config: `phpstan.neon`

2. **Code Standards (PHPCS)**
   - WordPress Coding Standards
   - PHP 8.1+ compatibility checks
   - Config: `phpcs.xml`

3. **PHPUnit Snapshot Tests**
   - Tests block HTML output
   - Catches unintended rendering changes
   - Requires explicit snapshot updates
   - Location: `tests/phpunit/snapshot/`

4. **Playwright E2E Tests**
   - Smoke tests for map rendering
   - Validates JavaScript execution
   - Location: `tests/playwright/`

5. **CodeQL Security Scan**
   - Automated security analysis
   - Runs on every PR in CI

### Docker Environment

Three containers:
- **db**: MySQL 8.0 database
- **wordpress**: WordPress 6.6 + PHP 8.1
- **cli**: WP-CLI for automation

WordPress available at `http://localhost:8080` (admin / password)
Test page: `http://localhost:8080/test-map/`

## Workflow

### Local Development

```bash
# Start working
make up              # Start containers
make setup           # First-time setup

# Make changes to code
npm run build        # Rebuild JS

# Verify changes
make test            # Run all checks
make playwright      # Test in browser

# Clean up
make down            # Stop containers
```

### Updating Snapshots

When block output **intentionally** changes:

```bash
# Update snapshots
make update-snapshots

# Review changes
git diff tests/phpunit/fixtures/

# Commit with explanation
git add tests/phpunit/fixtures/
git commit -m "Update snapshots: add ARIA labels to map container"
```

**Every snapshot update PR MUST include:**
1. Updated fixture files
2. Changelog entry
3. Rationale explaining the change

### Adding New Tests

**Snapshot test:**
```php
// tests/phpunit/snapshot/BlockSnapshotTest.php
public function test_my_new_feature(): void {
    $attributes = [ /* ... */ ];
    $output = render_block([
        'blockName' => 'ootb/openstreetmap',
        'attrs' => $attributes
    ]);
    $normalized = $this->normalize_output($output);
    $this->assertMatchesSnapshot($normalized);
}
```

**Playwright test:**
```typescript
// tests/playwright/my-feature.spec.ts
import { test, expect } from '@playwright/test';

test('my feature works', async ({ page }) => {
  await page.goto('/test-map/');
  await expect(page.locator('.my-feature')).toBeVisible();
});
```

## CI Pipeline

GitHub Actions workflow (`.github/workflows/ci.yml`):

```
┌─────────────────────────────────────────────────┐
│  static-analysis    js-build       security     │
│  (PHPStan+PHPCS)   (npm build)    (CodeQL)      │
└──────────────────────┬────────────┬─────────────┘
                       │            │
              ┌────────┴───────┐    │
              │                │    │
         ┌────▼────┐      ┌───▼────▼─┐
         │ phpunit │      │playwright│
         │(MySQL)  │      │ (Docker) │
         └─────────┘      └──────────┘
```

All jobs run in parallel except:
- PHPUnit waits for JS build (needs assets)
- Playwright waits for JS build (needs assets)

## Commands Reference

### Make Commands

| Command | Description |
|---------|-------------|
| `make up` | Start Docker containers |
| `make down` | Stop containers (keeps data) |
| `make setup` | Full setup: start + install WP + create fixtures |
| `make build` | Build JS assets |
| `make test` | Run all checks (lint + PHPUnit) |
| `make lint` | PHPStan + PHPCS only |
| `make phpunit` | PHPUnit tests only |
| `make update-snapshots` | Update snapshot fixtures |
| `make playwright` | Run browser tests |

### Manual Commands

```bash
# Composer
composer install                # Install dev dependencies
vendor/bin/phpstan analyse      # Static analysis
vendor/bin/phpcs                # Code standards
vendor/bin/phpunit              # Run tests
vendor/bin/phpunit --filter=test_name  # Single test

# Docker
docker compose up -d            # Start in background
docker compose down -v          # Stop and remove volumes
docker compose logs wordpress   # View logs
docker compose exec cli wp ...  # Run WP-CLI commands

# Playwright
npx playwright test             # Run all tests
npx playwright test --headed    # With browser UI
npx playwright test --debug     # Debug mode
npx playwright codegen http://localhost:8080  # Record test
```

## File Structure

```
.
├── .github/
│   └── workflows/
│       └── ci.yml              # GitHub Actions CI pipeline
├── bin/
│   └── install-wp-tests.sh     # WP test suite installer
├── docs/
│   └── tests/
│       ├── README.md           # Quick reference
│       └── TROUBLESHOOTING.md  # Common issues
├── scripts/
│   └── wp-test-setup.sh        # Docker WP setup
├── tests/
│   ├── phpunit/
│   │   ├── bootstrap.php       # PHPUnit bootstrap
│   │   ├── fixtures/           # Snapshot files (auto-generated)
│   │   └── snapshot/
│   │       └── BlockSnapshotTest.php
│   └── playwright/
│       ├── frontend-render.spec.ts   # Browser test
│       └── screenshots/        # Test screenshots (gitignored)
├── composer.json               # PHP dependencies + autoload
├── docker-compose.yml          # Local test environment
├── Makefile                    # Convenience commands
├── phpcs.xml                   # Code standards config
├── phpstan.neon                # Static analysis config
├── phpunit.xml.dist            # PHPUnit config
├── playwright.config.ts        # Playwright config
└── TESTING.md                  # This file
```

## Environment Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `WP_TESTS_DIR` | `/tmp/wordpress-tests-lib` | PHPUnit test suite location |
| `WP_BASE_URL` | `http://localhost:8080` | WordPress URL for Playwright |
| `UPDATE_SNAPSHOTS` | *(unset)* | Set to `1` to update snapshots |

## Requirements

- **PHP**: 8.1 or higher
- **Composer**: 2.x
- **Node.js**: 20 or higher
- **Docker**: 20.10+ with Compose v2
- **Git**: For version control and CI

## Troubleshooting

See [docs/tests/TROUBLESHOOTING.md](docs/tests/TROUBLESHOOTING.md) for common issues and solutions.

Quick fixes:
```bash
# Database connection issues
docker compose down -v && make setup

# Permission issues
sudo chown -R $USER:$USER .

# Clean slate
docker compose down -v
rm -rf vendor/ node_modules/ build/
composer install && npm ci && npm run build
make setup
```

## CI Badges

Add to README.md:

```markdown
[![CI](https://github.com/YOUR_USERNAME/ootb-openstreetmap/workflows/CI/badge.svg)](https://github.com/YOUR_USERNAME/ootb-openstreetmap/actions)
```

## Best Practices

1. **Always run tests before pushing**
   ```bash
   make test && make playwright
   ```

2. **Keep snapshots up to date**
   - Update immediately after intentional output changes
   - Document why in commit message

3. **Write deterministic tests**
   - Use fixed IDs and coordinates
   - Normalize dynamic content (nonces, versions)

4. **Test in Docker**
   - Matches CI environment
   - Isolates from local configuration

5. **Monitor CI**
   - Check Actions tab after pushing
   - Fix failures before merging

## Further Reading

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Playwright Documentation](https://playwright.dev/)
- [WordPress Plugin Testing](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)
- [GitHub Actions](https://docs.github.com/en/actions)
