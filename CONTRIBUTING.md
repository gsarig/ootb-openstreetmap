# Contributing to OOTB OpenStreetMap

Thank you for your interest in contributing! This document covers everything you need to know — whether you're submitting a bug fix, a new feature, or just getting your local environment set up.

---

## Table of Contents

- [Branching model](#branching-model)
- [How to submit a pull request](#how-to-submit-a-pull-request)
- [Local setup](#local-setup)
- [Code standards](#code-standards)
- [Testing](#testing)
- [What counts as a breaking change](#what-counts-as-a-breaking-change)
- [JS/PHP boundary](#jsphp-boundary)
- [Maintainer workflow](#maintainer-workflow)

---

## Branching model

- `master` is the stable branch. It reflects the latest released version.
- Feature work and bug fixes land in release branches (`release/X.Y.Z`) before being merged to `master`.
- **You don't need to know about release branches.** Just open your PR against `master` — the maintainer will retarget it to the active release branch before merging.

---

## How to submit a pull request

1. Fork the repository and create your branch from `master`.
2. Name your branch descriptively: `fix/marker-drag-bug`, `feature/cluster-support`, etc.
3. Make your changes (see [Code standards](#code-standards) and [Testing](#testing) below).
4. Open a pull request against `master`.
5. Describe what the PR does and why. If it fixes a bug, link the issue.

The maintainer will retarget the PR to the current release branch before merging.

---

## Local setup

**Requirements:** PHP 8.1+, Composer, Node.js 20+, Docker

```bash
composer install
npm ci && npm run build
npx playwright install
make setup        # Starts Docker, installs WordPress, creates test fixtures
```

WordPress runs at `http://localhost:8080` (admin: `admin` / `password`).

---

## Code standards

### PHP

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) — enforced by PHPCS (`phpcs.xml`).
- Code must pass [PHPStan](https://phpstan.org/) at **level 6** (`phpstan.neon`). No errors allowed.
- PHP 8.1+ — use typed properties, union types, and named arguments where appropriate.
- Escape all output. Sanitize and validate all input.
- No magic numbers or hardcoded strings — use constants or configuration.

Run the checks:

```bash
make lint
```

### JavaScript

- ES modules, `async/await`.
- Follow the existing patterns in `assets/js/` and `src/block/`.
- Run `npm run build` after any JS change to verify the build completes without errors.

---

## Testing

All three checks must pass before a PR can be merged.

```bash
make lint         # PHPStan + PHPCS — run first, fastest
make phpunit      # PHPUnit snapshot tests — requires Docker
make playwright   # Playwright E2E tests — slowest, run last
```

Or run lint and PHPUnit together:

```bash
make test
```

### Snapshot tests

PHPUnit snapshot tests in `tests/phpunit/snapshot/` define the expected HTML output of the block. If your change intentionally alters the rendered HTML, say so clearly in your PR description — snapshots cannot be auto-updated without explicit sign-off.

---

## What counts as a breaking change

This plugin is published on WordPress.org and has real users. The following are part of the **public API** and must not be changed without a clear, intentional reason:

| Type | Examples |
|------|---------|
| Filter hooks | `ootb_query_post_type`, `ootb_query_posts_per_page`, `ootb_query_extra_args`, `ootb_cf_modal_content`, `ootb_block_marker_text`, `ootb_cf_marker_icon` |
| Shortcode attributes | `[ootb_query]` attribute names |
| Block attribute names | Any attribute defined in `src/block/block.json` |

If your PR changes any of these, flag it explicitly in the PR description.

---

## JS/PHP boundary

The plugin has two distinct layers:

- **PHP** — block registration, server-side rendering, REST endpoints, settings.
- **JavaScript** — Gutenberg block editor (React), Leaflet.js frontend.

Most contributions will touch one layer only. If your change crosses both, call it out in the PR description so it can be reviewed carefully.

---

## Maintainer workflow

This section is for the maintainer only.

### Handling external PRs

Contributors open PRs against `master`. Before merging:

1. **Review the PR** on `master` as usual.
2. **Retarget to the active release branch** — in the PR, click "Edit" next to the title and change the base branch from `master` to `release/X.Y.Z`. GitHub will recompute the diff automatically.
3. **Check for conflicts.** If there are none, merge normally. If there are conflicts, either:
   - Ask the contributor to rebase onto the release branch, or
   - Cherry-pick the commits onto the release branch manually:
     ```bash
     git fetch origin
     git checkout release/X.Y.Z
     git cherry-pick <commit-sha>
     ```
4. **Run the full test suite** before merging:
   ```bash
   make lint && make phpunit && make playwright
   ```

### Release branch naming

```
release/X.Y.Z
```

Cut a new release branch from `master` when starting work on a new version:

```bash
git checkout master
git pull
git checkout -b release/X.Y.Z
git push -u origin release/X.Y.Z
```
