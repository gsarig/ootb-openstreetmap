# Copilot Instructions

## Project overview

OOTB OpenStreetMap is a WordPress plugin that renders interactive maps using Leaflet.js
as a Gutenberg block. It is published on WordPress.org and has real users.

Two distinct layers:
- **PHP** — block registration, server-side rendering, REST endpoints, settings, shortcodes
- **JavaScript** — Gutenberg block editor (React), Leaflet.js frontend

## Coding standards

- **CLAUDE.md** is the authoritative source of coding rules for this project. All review
  comments must be consistent with it.
- PHP follows **WordPress Coding Standards** (enforced by PHPCS).
- PHP static analysis runs at **PHPStan level 6** — no errors allowed.
- JavaScript uses ES modules and async/await.
- All output must be escaped; all input must be sanitised and validated.

## What to focus on

Review for **correctness, security, and reliability** — in that order.

- Correctness: bugs, off-by-one errors, undefined variables, broken async, wrong return types
- Security: unvalidated input, data reaching external APIs without sanitisation,
  missing allowlist checks, regex patterns that match unintended substrings
- Reliability: missing pagination on list API calls, unhandled failure paths,
  operations that can loop or retry indefinitely

## What to skip

- **Style**: indentation, quote style, semicolons, naming conventions — these are
  enforced by PHPCS and ESLint. Do not comment on them.
- **Configurability**: do not suggest making intentional hardcoded values configurable
  unless there is a concrete correctness or security reason to do so.
- **Speculative edge cases**: only flag an edge case if it is realistically reachable
  given the surrounding code. Do not flag structurally unreachable paths.
- **Suggestions**: if a comment uses hedging language ("consider", "could", "might",
  "would be better"), it is a suggestion — only raise it if it addresses a real defect.
- **Version numbers**: `@since` tags, `OOTB_VERSION`, `package.json version`,
  `block.json version`, and `readme.txt stable tag` are bumped in a dedicated release
  commit just before tagging. Feature branches intentionally stay at the previous version.
  Do not flag version fields as inconsistent or mismatched during feature branch reviews.

## Public API — breaking change alert

The following are part of the plugin's public API. Any change to them is a **breaking
change** and must be flagged explicitly:

- PHP filter hooks: `ootb_query_post_type`, `ootb_query_posts_per_page`,
  `ootb_query_extra_args`, `ootb_cf_modal_content`, `ootb_block_marker_text`,
  `ootb_cf_marker_icon`
- Shortcode: `[ootb_query]` and its attributes
- Block attribute names in `block.json`

## Snapshot tests

PHPUnit snapshot tests in `tests/phpunit/snapshot/` define the expected HTML output
of the block. Any implementation change that affects rendered HTML will break them.
Flag this explicitly — snapshot updates require a deliberate decision by the author.

## CI workflows

The workflows in `.github/workflows/` contain intentional design decisions:

- Guard conditions (PR author, base branch, labels, draft status) are deliberate
  constraints, not undocumented behaviour. Do not flag them as needing documentation
  or suggest making them configurable.
- The `copilot-review-poller.yml` + `copilot-cr-fix.yml` pair uses a scheduled
  polling approach to bypass GitHub's bot-triggered approval gate — this is
  intentional architecture, not a workaround to remove.
