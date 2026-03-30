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
