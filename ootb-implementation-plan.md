# OOTB OpenStreetMap — Implementation Plan

A phased roadmap for building an AI-assisted development, testing, release, and maintenance workflow.

---

## Overview

This plan covers the full development lifecycle: testing infrastructure, feature workflow, release management, and ongoing maintenance automation.

**Guiding principles:**
- Build incrementally — each phase delivers value on its own
- Keep the plugin repo clean — tooling lives in a separate repo
- Automate the mechanical, stay in the loop on decisions
- The final merge to master is always manual

---

## Phase 1 — Foundation

*CLAUDE.md · Project files · GitHub setup*

Everything else depends on this phase. Get the foundations right before writing any automation code.

### 1.1 CLAUDE.md (done)

Your CLAUDE.md is already written and covers: minimal impact rule, minimal file changes rule, PHP vs JS boundary warning, public API stability, snapshot test discipline, codebase structure, commands, testing workflow, and code style. Place it at the project root and commit it.

### 1.2 GitHub Project + Roadmap

One-time manual setup in the GitHub UI before building any automation:

- Create a GitHub Project linked to the ootb-openstreetmap repo
- Enable the Roadmap view
- Add custom fields: Status, Priority, Milestone, Type (feature / bug / maintenance)
- Create at least two milestones: current release and next release

This is the persistent source of truth the planning agent will read from and write to.

### 1.3 .distignore file

Create `.distignore` at the plugin root. It controls what gets excluded from the WordPress.org zip. Typical exclusions:

- `.git`, `.github`, `node_modules`, `tests`, `.distignore`, `.gitignore`
- `composer.json`, `composer.lock`, `package.json`, `package-lock.json`
- `phpcs.xml`, `phpstan.neon`, `phpunit.xml.dist`, `playwright.config.ts`
- `Makefile`, `docker-compose.yml`, `CHANGELOG.md`, `README.md` (GitHub version)

Note: `readme.txt` stays in — that is the WordPress.org readme, not the GitHub README.

### 1.4 Create the tooling repo

Create a separate repository — `ootb-dev-tools` or similar. This never gets deployed. It knows about the plugin; the plugin does not know about it.

---

## Phase 2 — Testing Infrastructure

*Snapshots · Artifact test · Copy review agent*

The most impactful investment for code quality. Each part here is independent — implement in any order.

### 2.1 Comprehensive PHPUnit snapshot tests

Your existing snapshot tests cover basic cases. Expand to cover all meaningful attribute combinations. Use a dedicated Claude Code session with this prompt pattern:

> Read CLAUDE.md, then read `block.json` and the existing `BlockSnapshotTest.php`. List all attribute combinations you plan to cover — wait for my confirmation. After confirmation, write the tests following existing conventions. Run `make phpunit` to generate fixture files. Report: how many cases, what was covered, what was intentionally skipped and why. Do not update any existing fixtures.

Priority combinations to cover:
- All map types: markers, polygon, polyline
- All providers: OpenStreetMap, MapBox, Stamen
- Single vs multiple markers
- Custom marker icons, markers with popup content
- Behaviour attributes: dragging disabled, scroll zoom, touch zoom, zoom locked
- Custom height
- Query Maps output
- Shortcode output

### 2.2 Distribution artifact test (done)

Tests the actual zip that WordPress.org serves, not your source code. Lives in this repo — not `ootb-dev-tools` — because the Playwright tests and Docker infrastructure already live here. Splitting them across repos would require duplicating the test suite or building a fragile cross-repo trigger.

**What was built:**

| File | Purpose |
|------|---------|
| `bin/build-zip.sh` | Builds the distribution zip: `npm ci && npm run build`, then `composer install --no-dev` inside a temp build dir (workspace untouched), then rsync + zip |
| `.github/workflows/release.yml` | Fixed to run the same build steps before the 10up deploy action — previously deployed with dev vendor and stale assets |
| `docker-compose.artifact.yml` | Standalone compose for artifact testing — no source volume mount; mounts `/tmp/ootb-artifact` so WP-CLI can install the zip |
| `scripts/wp-artifact-setup.sh` | WordPress setup for the artifact job: installs from zip via `wp plugin install`, creates test page, sets options |
| `ci.yml` — `artifact-playwright` job | Runs the full pipeline: version check → build zip → install to clean WP → Playwright tests |

**The artifact job pipeline:**

| Step | Action | Detail |
|------|--------|--------|
| 1 | Verify version consistency | PHP header, `package.json`, `readme.txt` Stable tag must all match — fast gate, fails before any build |
| 2 | Build the zip | `bin/build-zip.sh` — mirrors exactly what `release.yml` deploys to WordPress.org |
| 3 | Install to clean WP | Spin up `docker-compose.artifact.yml`, install zip via WP-CLI |
| 4 | Run Playwright suite | Same tests as the source-based job, but against the installed zip |

**Why Playwright only (no PHPUnit):** PHPUnit snapshot tests run against the PHP render layer directly and are not meaningful against an installed zip. The artifact job's value is confirming that assets load, the plugin activates, and the frontend renders correctly from the distribution package.

**Version consistency check** — three sources checked in CI (a PHP constant check can be added if one is introduced):
- `Version:` header in `ootb-openstreetmap.php`
- `version` in `package.json`
- `Stable tag:` in `readme.txt`

**Added to CLAUDE.md** under Critical Development Rules — "Distribution Zip Is the Real Artefact".

### 2.3 Copy review agent

A dedicated session that reads changed files after implementation and reviews all user-facing strings for quality, consistency, and translation implications. Runs in parallel with the test session — neither depends on the other's output.

- **Scope:** block inspector labels, toolbar titles, placeholder text, error messages, settings copy, all `__()` and `_e()` translation strings
- **Output:** a list distinguishing actual errors from stylistic suggestions, with translation string changes flagged separately
- **Note:** this is not a code review — it is purely linguistic

---

## Phase 3 — Feature Workflow

*Implementation · Testing · Handoff · Changelog*

The day-to-day development loop. Two terminal sessions, intentional prompts, clean handoff between them.

### 3.1 Two-session workflow

**Session A — Implementer**
- Interactive — you stay in the loop
- Reads CLAUDE.md, implements the feature
- Runs `make lint` + `make phpunit`
- Produces structured handoff summary

**Session B — Tester + Copy**
- Receives handoff summary as starting context
- Checks if tests exist for new feature
- Proposes test cases — waits for your confirmation
- Writes tests + reviews copy strings

### 3.2 Handoff summary format

Session A produces this at the end of every implementation task. It serves three purposes: context for Session B, source material for the changelog, and brief for the feature PR description.

```
FEATURE:           one sentence — what this does for the user
FILES CHANGED:     list with one-line reason per file
EXPECTED BEHAVIOUR: main cases and edge cases
ASSUMPTIONS MADE:  anything uncertain or inferred
CHANGELOG DRAFT:   user-facing one-liner for readme.txt
```

### 3.3 Snapshot discipline reminder

When Session B finds that snapshot tests fail due to an intentional HTML change, it must stop and report — not auto-update. The update requires your explicit confirmation and must be accompanied by a changelog entry and rationale in the PR description.

---

## Phase 4 — Release Pipeline

*Branches · PRs · GitHub Roadmap · Artifact gate*

Automates the mechanical parts of release management while keeping all decisions in your hands. Built in `ootb-dev-tools` as Node.js scripts using the `gh` CLI.

### 4.1 Release workflow

| Step | Action | Detail |
|------|--------|--------|
| 1 | Start release | You run: `node scripts/release-pipeline.js start 3.x` — creates `release/3.x` branch, opens draft release PR |
| 2 | Per feature | Planning agent (or you) creates feature branch from release branch, opens draft feature PR with full description |
| 3 | Implement | You open a Claude Code session, read the feature PR description as your brief, implement |
| 4 | Test + review | Session B runs tests and copy review, reports back |
| 5 | Copilot CR + auto-fix | Copilot posts its review asynchronously. A GitHub Action fires on `review_submitted`, reads the comments, calls the Anthropic API with the relevant files and CLAUDE.md rules, and pushes a fixup commit to the feature branch. You are notified to review the commit. |
| 6 | Approve + merge | You review the fixup commit, approve, and merge the feature PR to the release branch. Release PR description updates automatically. |
| 7 | Artifact gate | Before marking ready for review, artifact test runs against the release branch |
| 8 | Prepare release | Agent reads all merged feature summaries, writes marketing-friendly release notes, marks PR ready |
| 9 | Final merge | You review, add screenshots/videos, merge to master — triggers WordPress.org deployment |

### 4.2 Copilot CR auto-fix workflow

This step runs entirely in the cloud — your terminal does not need to be open.

The trigger is a GitHub Action in the plugin repo:

```yaml
on:
  pull_request_review:
    types: [submitted]
```

When Copilot submits its review, the Action:

1. Checks the reviewer is Copilot (ignores human reviews)
2. Fetches all review comments via `gh` CLI
3. Calls the Anthropic API directly (not Claude Code) with the comments, the relevant file contents, and your CLAUDE.md rules as context
4. Pushes a fixup commit to the feature branch with the proposed fixes
5. Posts a PR comment summarising what was changed and why

You then review the fixup commit — approve it if it looks right, push your own correction if it doesn't. Nothing merges automatically.

**Why the Anthropic API and not Claude Code?** Claude Code is an interactive local process — it can't wait around for an event that happens twenty minutes after you've closed your terminal. The API is stateless and event-driven, which is exactly what a GitHub Action needs.

### 4.3 CR fix rules (`agents/cr-fix.md`)

The agent's behaviour is governed by `agents/cr-fix.md` in `ootb-dev-tools`. This file is the system prompt for the Anthropic API call — without it, the agent will blindly apply every suggestion, which is not what you want.

**Decision criteria — apply the fix if:**
- It is a clear correctness issue (bug, typo, undefined variable, wrong return type)
- It aligns with CLAUDE.md conventions and code style
- It affects only the files already changed in the PR
- It does not touch public API surface (hooks, shortcode attributes, block attributes, REST endpoints)
- It does not require changes to snapshot test fixtures without your explicit confirmation

**Skip the fix and reply to the comment if:**
- It contradicts a rule in CLAUDE.md
- It would change a public API (breaking change for plugin users)
- It is a stylistic preference with no correctness implication
- It would require modifying files outside the PR's current scope
- It would require updating snapshot fixtures (flag it instead — you decide)
- The suggestion is ambiguous and the agent isn't confident what the right fix is

**Required behaviour for every comment, without exception:**
- Applied fix → reply to the comment describing exactly what was changed and why
- Skipped fix → reply to the comment explaining why it was skipped, mark it resolved
- Ambiguous → reply flagging the uncertainty, skip, do not guess

The agent must never apply a fix silently. Every Copilot comment must get a reply — either a confirmation of the fix or an explanation of why it was skipped. This gives you a full audit trail in the PR without having to diff the files to understand what the agent did or didn't do.

**What `agents/cr-fix.md` should contain:**

```markdown
You are a code fix agent for the OOTB OpenStreetMap WordPress plugin.
You have been given a set of Copilot code review comments on a pull request.

Your job is to evaluate each comment and either apply the fix or skip it.
You must reply to every comment — never leave one without a response.

## Primary reference
Read CLAUDE.md before evaluating any comment. All fixes must comply with it.

## Apply the fix when
- It is a correctness issue (bug, typo, undefined variable, wrong return type)
- It aligns with CLAUDE.md conventions
- It only affects files already changed in this PR
- It does not touch public API surface (hooks, shortcode attributes, block attributes)
- It does not require snapshot fixture updates

## Skip the fix and reply when
- It contradicts CLAUDE.md
- It would change a public API
- It is stylistic preference only
- It requires changes outside this PR's scope
- It requires snapshot fixture updates — flag these explicitly for the author
- You are not confident what the correct fix is

## Reply format for applied fixes
> Applied: [one sentence describing the change and why it addresses the comment]

## Reply format for skipped fixes
> Skipped: [one sentence explaining why — reference the specific CLAUDE.md rule
> or reason. Mark as resolved.]

## Reply format for ambiguous comments
> Unclear: [one sentence describing the ambiguity]. Skipped — please clarify
> and I will re-evaluate.

## Never
- Apply a fix without leaving a reply
- Update snapshot fixtures — always flag these for the author instead
- Modify files outside the PR's current diff
- Change anything that affects the public plugin API
```

### 4.4 Feature PR description format

The planning agent writes each draft feature PR in a format that doubles as a Claude Code brief — so each implementation session starts with full context, not a blank slate.

```markdown
## What
One sentence — what this does for the user

## Why
Links to GitHub issues and/or WordPress.org forum posts

## Scope
- Specific thing to implement
- Specific thing to implement
- Out of scope: X — do not implement this

## Where in the codebase
Relevant files/folders based on similar past work

## Acceptance criteria
- [ ] Specific verifiable outcome
- [ ] Specific verifiable outcome

## Notes
Caveats, patterns to follow, related issues
```

### 4.5 GitHub Roadmap integration

The planning agent reads and writes to the GitHub Project Roadmap. When you approve the plan, you can say: *implement features 1 and 2 now, put feature 3 in the next release*. The agent moves items accordingly — next release items land in the roadmap without creating branches or PRs yet.

The roadmap also becomes your public changelog of intent — users can see planned work without you manually responding to every forum post.

---

## Phase 5 — Autonomous Agents

*Planning · Compatibility · Orchestration*

The highest-leverage phase — but also the most complex to build. Start only after Phase 4 is running smoothly. Each agent here is independent and can be built separately.

### 5.1 Planning agent

Runs at the start of each release cycle. Produces a prioritized roadmap proposal for your approval — it never acts without your sign-off.

- Reads all open GitHub issues
- Scrapes WordPress.org support forum for the plugin
- Clusters related reports, scores by frequency / severity / recency
- Cross-references with current GitHub Roadmap (avoids re-surfacing resolved items)
- Presents prioritized proposal with reasoning
- On your approval: creates release branch, draft release PR, draft feature PRs with full descriptions
- On your deferral: moves items to next release milestone in GitHub Roadmap

### 5.2 Compatibility check agent

Runs monthly on a schedule (or manually before each planning cycle). Pure research — no code changes, no git operations.

**What it monitors:**
- WordPress core: upcoming releases, deprecations affecting block API and server-side rendering
- Gutenberg plugin changelog: block editor API changes
- PHP release schedule and EOL dates — cross-referenced against your codebase
- Leaflet.js releases — checked against version in `package.json`
- npm dependencies: outdated packages via `npm outdated`
- `composer.json` runtime dependencies: compatibility with upcoming PHP versions

Output is three-tier: **ACTION REQUIRED** / **MONITOR** / **NO ACTION NEEDED**. If anything is action required, the agent opens a GitHub issue tagged `maintenance` automatically. The planning agent picks this up in the next cycle.

### 5.3 Orchestration script

Automates the handoff between Session A and Session B so you don't manually copy-paste between terminal tabs. Built in `ootb-dev-tools`.

| Step | Action | Detail |
|------|--------|--------|
| 1 | You finish Session A | Implementation complete, you confirm you are happy |
| 2 | Session A outputs | Structured handoff summary saved to `handoff.tmp` |
| 3 | Script triggers | `node orchestrate.js` reads `handoff.tmp`, spawns Session B |
| 4 | Session B runs | Tests, copy review, reports back automatically |
| 5 | Script reports | Combined output: test results + copy suggestions + changelog draft |
| 6 | Cleanup | `handoff.tmp` deleted on success, kept on failure for resume |

**Build order for `ootb-dev-tools` scripts:**
1. `compatibility-check.js` — simplest, no git ops, pure research, proves the approach
2. `planning-pipeline.js` — research + roadmap, without branch/PR creation first
3. Extend `planning-pipeline.js` — add branch and PR creation once research is solid
4. `orchestrate.js` — handoff automation between sessions
5. `release-pipeline.js` — ties everything together

Note: artifact testing lives in the plugin repo (see §2.2), not here.

---

## Where to Start

Do not try to build everything at once. This is the recommended order, each step independently useful:

| # | Step | Detail |
|---|------|--------|
| 1 | Commit CLAUDE.md | Done |
| 2 | .distignore | Done |
| 3 | Snapshot tests | Done — BlockSnapshotTest, BlockAttributeSnapshotTest (12 cases), QuerySnapshotTest |
| 4 | Fix release.yml + build-zip.sh | Done — build steps added, dev vendor no longer deployed |
| 5 | Artifact test CI job | Done — docker-compose.artifact.yml, wp-artifact-setup.sh, artifact-playwright job in ci.yml |
| 6 | Add artifact note to CLAUDE.md | Done |
| 7 | GitHub Project | One-time manual setup in GitHub UI |
| 8 | Create ootb-dev-tools | `mkdir`, `git init`, done — empty repo ready for scripts |
| 9 | compatibility-check.js | First script — self-contained, proves tooling works |
| 10 | Two-session workflow | Start using it manually — no code needed |
| 11 | orchestrate.js | Only after two-session workflow is proven in practice |
| 12 | planning-pipeline.js | Once the release workflow is running smoothly |
| 13 | Full pipeline | When all pieces work individually, connect them |

---

## Files & Repos Reference

**`ootb-openstreetmap/` (plugin repo)**
- `CLAUDE.md` — always loaded by Claude Code
- `.distignore` — controls WordPress.org zip
- `readme.txt` — WordPress.org readme
- `bin/build-zip.sh` — builds distribution zip (mirrors release.yml exactly)
- `docker-compose.artifact.yml` — standalone compose for artifact testing (no source mount)
- `scripts/wp-artifact-setup.sh` — WP setup for artifact job (installs from zip)
- `tests/` — PHPUnit + Playwright
- `.github/workflows/ci.yml` — includes `artifact-playwright` job
- `Makefile` — all standard commands

**Repo boundary rationale:** artifact testing lives here, not in `ootb-dev-tools`, because
the Playwright tests, Docker config, and test fixtures all live here. Moving them would mean
duplicating the test suite or building a fragile cross-repo trigger.

**`ootb-dev-tools/` (tooling repo)**
- `orchestrate.js` — session handoff automation
- `agents/implementer.md` — Session A prompt
- `agents/tester.md` — Session B prompt
- `agents/copy-review.md` — copy agent prompt
- `agents/planning.md` — planning agent prompt
- `agents/compatibility.md` — compat agent prompt
- `agents/cr-fix.md` — Copilot CR fix rules and reply format
- `scripts/planning-pipeline.js`
- `scripts/compatibility-check.js`
- `scripts/release-pipeline.js`
- `.env` — GITHUB_TOKEN, repo name

---

## Phase 6 — WordPress Ecosystem Integration

*MCP Adapter · WP AI Client · Studio · Future-proofing*

This phase is not about building anything today. It is about watching the right things and being ready to act when the ecosystem catches up. Automattic is shipping AI infrastructure at pace — each piece below has direct implications for OOTB.

### 6.1 WordPress MCP Adapter

Shipped in February 2026 on top of the Abilities API introduced in WordPress 6.9. Any AI tool that speaks MCP — Claude Code, Claude Desktop, Cursor, VS Code — can now discover and call WordPress capabilities directly. A plugin registers an ability, requires the adapter, and becomes AI-ready with minimal extra work.

Two directions this opens up for OOTB:

- Your `ootb-dev-tools` compatibility and planning agents could query a live WordPress instance directly via MCP — checking block rendering, REST endpoint responses — without a separate Docker environment
- OOTB itself could expose block capabilities as MCP abilities, meaning an AI tool could add or modify markers programmatically through the plugin's own registered interface — a more robust foundation than the current natural-language AI integration

**Watch for:**
- MCP Adapter stability and documentation reaching maturity
- Adoption by other block plugins as a reference implementation pattern
- Whether registering block capabilities as MCP abilities becomes a WordPress.org recommendation

### 6.2 WP AI Client merging into WordPress 7.0 core

Proposed for WordPress 7.0 — native, provider-agnostic AI query capability built into core. No API key setup, no plugin hunting required on compatible hosts.

OOTB already has an AI integration for natural-language marker placement, but it requires users to configure their own API provider and key. If core ships an AI client, OOTB could hook into that instead — removing setup friction entirely for the majority of users while keeping the bring-your-own-key option for those who want it.

**Watch for:**
- WordPress 7.0 beta and the WP AI Client merge confirmation
- The provider-agnostic API surface — ensure OOTB's AI integration would be compatible
- How WordPress.com and other hosts configure the default provider

### 6.3 Content Guidelines as a Gutenberg feature

A proposal to give site owners a first-class place inside WordPress to define editorial rules and site context. Described as the foundation that lets agents understand why a site publishes the way it does — heading toward a core feature.

For OOTB, this is a longer-term opportunity: the AI marker placement feature could eventually respect site-level content guidelines. A site that defines a geographic focus could automatically constrain what locations Claude suggests, without requiring per-block configuration.

**Watch for:**
- The Content Guidelines proposal progressing through the Gutenberg experiment phase
- Whether a stable API surface emerges that plugins can hook into
- Community adoption — the feature is only useful if sites actually define their guidelines

### 6.4 WordPress Studio as a development environment

Studio 1.7.0 shipped CLI coverage for nearly every feature, making it fully compatible with Claude Code and Cursor. It is purpose-built for agent-driven WordPress development and is described as having significantly more capability on the way.

Worth evaluating as a replacement or complement to the current Docker environment for local development and Playwright testing. The advantage over Docker is that it is maintained by Automattic specifically for this kind of AI-assisted workflow — when Claude Code needs a running WordPress instance, Studio may be the path of least resistance.

**Practical next step:**
- Install WordPress Studio and run the existing Playwright test suite against it
- Compare the setup experience and test reliability against the current Docker environment
- If Studio proves more reliable, update CLAUDE.md and the testing workflow accordingly

### 6.5 The broader through-line

The Automattic strategy is infrastructure first, then product on top: OAuth → MCP Adapter → Connectors → AI features. Each layer made the next one faster to ship. This is directly analogous to the sequencing in this implementation plan — foundations before automation, automation before agents.

The practical implication for OOTB: the investments in Phases 1–5 are not throwaway work when the ecosystem evolves. A well-structured CLAUDE.md, clean test infrastructure, and a thoughtful release pipeline are the foundation that makes it straightforward to adopt MCP, the WP AI Client, and whatever comes after. The plugins that will integrate these capabilities fastest are the ones with disciplined codebases and good tooling — not the ones scrambling to retrofit.

---

*The goal is not to automate everything. It is to automate the mechanical so you can focus on the decisions that actually matter.*
