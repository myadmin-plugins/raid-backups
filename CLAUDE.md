# MyAdmin RAID Backups Plugin

MyAdmin plugin providing event-driven RAID backup management. Package type: `myadmin-plugin`. Namespace: `Detain\MyAdminRaid\` → `src/`.

## Commands

```bash
composer install                        # install deps
vendor/bin/phpunit                      # run tests
vendor/bin/phpunit tests/ -v            # verbose
```

```bash
# Static analysis and code quality
vendor/bin/phpcs src/ tests/            # check coding standards
vendor/bin/php-cs-fixer fix src/        # fix code style
```

```bash
# Coverage reporting
vendor/bin/phpunit --coverage-text      # text coverage report
vendor/bin/phpunit --coverage-html coverage/  # HTML report
```

## Architecture

- **Plugin entry**: `src/Plugin.php` · class `Detain\MyAdminRaid\Plugin`
- **Event system**: `symfony/event-dispatcher` `^5|^6|^7` · uses `Symfony\Component\EventDispatcher\GenericEvent`
- **Hook methods**: `getHooks()` · `getMenu(GenericEvent)` · `getRequirements(GenericEvent)` · `getSettings(GenericEvent)`
- **Tests**: `tests/PluginTest.php` · namespace `Detain\MyAdminRaid\Tests\` · PHPUnit 9.6 · config `phpunit.xml.dist`
- **Autoload**: PSR-4 `Detain\MyAdminRaid\` → `src/` · dev `Detain\MyAdminRaid\Tests\` → `tests/`
- **Quality**: `.scrutinizer.yml` · `.codeclimate.yml` · `.bettercodehub.yml`
- **CI/CD**: `.github/` contains workflows for automated testing and deployment pipelines
- **IDE**: `.idea/` contains inspectionProfiles, `deployment.xml`, `encodings.xml`

## Conventions

- All hook methods on `Plugin` are `public static` accepting `GenericEvent $event`
- `getRequirements`: call `$loader->add_requirement('name', '/path/to/src/ClassName.php')`
- `getMenu`: check `$GLOBALS['tf']->ima == 'admin'` then `has_acl('client_billing')` before adding menu items
- `getSettings`: receive `$settings = $event->getSubject()` and configure
- Indentation: **tabs** (see `.scrutinizer.yml` `use_tabs: true`)
- Constants: UPPERCASE · properties/params: camelCase
- No closing PHP tag in files
- Commit messages: lowercase, descriptive

<!-- caliber:managed:pre-commit -->
## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
