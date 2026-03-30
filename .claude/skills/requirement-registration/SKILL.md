---
name: requirement-registration
description: Registers new class or function requirements via $loader->add_requirement() inside Plugin::getRequirements() in src/Plugin.php. Use when user says 'add requirement', 'register class', 'load function', or adds new source files to src/. Covers path pattern /../vendor/detain/myadmin-raid-backups/src/. Do NOT use for adding Composer dependencies or modifying other hook methods.
---
# Requirement Registration

## Critical

- Only edit `src/Plugin.php` — the `getRequirements` method is the sole registration point.
- The path argument must point to the correct file under `src/` using an absolute-style path with a `/../vendor/` prefix.
- Use **tabs** for indentation (never spaces) — enforced by `.scrutinizer.yml`.
- Class file names use the naming key `class.ClassName`. Function names use the bare function name (e.g. `deactivate_kcare`).
- Multiple functions from the same file each get their own `add_requirement()` line with the same path.
- Do NOT add a closing `?>` PHP tag.

## Instructions

1. **Identify the requirement key and file path.**
   - For a class: key = `'class.ClassName'`, path points to the class file under `src/`.
   - For a function: key = the bare function name, path points to the file containing it under `src/`.
   - Verify the target file exists under `src/` before proceeding.

2. **Open `src/Plugin.php` and locate `getRequirements()`** (currently at line 53).
   The method body always starts with:
   ```php
   $loader = $event->getSubject();
   ```
   Append new `add_requirement` calls after the last existing one. Never reorder existing lines.

3. **Add the call using this exact pattern (tab-indented):**
   ```php
   		$loader->add_requirement('key', '/path/to/src/ClassName.php');
   ```
   Use the key and path determined in Step 1.

4. **Update the test in `tests/PluginTest.php`.**
   - In `testGetRequirementsRegistersExpectedPaths()`: increment the `assertCount(N, ...)` value by the number of new entries added.
   - Add an `assertContains('new_key', $names)` line for each new requirement key.
   - Verify path assertions in `testGetRequirementsPathsReferenceVendorDirectory()` still hold — no change needed if path follows the standard pattern.

5. **Run tests to confirm:**
   ```bash
   vendor/bin/phpunit
   ```
   All tests must pass before finishing.

## Examples

**User says:** "Add a requirement for the new `BackupJob` class in `src/BackupJob.php` and the `queue_backup_job` function in `src/backup_jobs.inc.php`."

**Actions taken:**
- Verified `src/BackupJob.php` and `src/backup_jobs.inc.php` exist.
- Added to `getRequirements()` in `src/Plugin.php`:
  ```php
  		$loader->add_requirement('class.BackupJob', '/../vendor/myadmin-raid-backups/src/BackupJob.php');
  		$loader->add_requirement('queue_backup_job', '/../vendor/myadmin-raid-backups/src/backup_jobs.inc.php');
  ```
- Updated `testGetRequirementsRegistersExpectedPaths()`: changed `assertCount(4, ...)` → `assertCount(6, ...)`, added two `assertContains` lines.
- Ran `vendor/bin/phpunit` — all tests passed.

**Result:** `getRequirements()` now registers 6 requirements; the loader will resolve both the class and function on demand.

## Common Issues

- **Test fails with `assertCount(4, ...) but got 6`:** You added requirements without updating the count in `testGetRequirementsRegistersExpectedPaths()`. Update `assertCount` to match the new total.
- **Test fails with `Failed asserting that array contains 'class.Foo'`:** You updated the count but forgot to add the `assertContains` line for the new key.
- **Path assertion fails:** You used the wrong path prefix. Correct format requires the `/../vendor/` prefix followed by the vendor package path and `src/ClassName.php`.
- **PHP parse error / unexpected token:** Check that you used a **tab** character (not spaces) for indentation. The `.scrutinizer.yml` enforces `use_tabs: true`.
- **`vendor/bin/phpunit` command not found:** Run `composer install` first to populate `vendor/`.
