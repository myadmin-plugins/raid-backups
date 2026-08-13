---
name: phpunit-test
description: Creates PHPUnit 9.x test cases in `tests/` under `Detain\MyAdminRaid\Tests\` namespace following `tests/PluginTest.php` conventions. Use when user says 'add test', 'write unit test', 'test this method', or adds new methods to `src/Plugin.php`. Covers class structure tests, static property assertions, event handler signature tests, anonymous-class stubs, and source-file static analysis. Do NOT use for integration tests outside this plugin or tests against other packages. For a plugin's contract or behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
---
<!-- myadmin-contract-harness-notice -->
> ### ⚠️ Read this before the rest of the file
>
> This package is on the **shared plugin contract harness**. Parts of the guidance below
> predate it and are now wrong in one specific way:
>
> **Any instruction here that a plugin's `getHooks()` / `getSettings()` / `getActivate()` /
> `getDeactivate()` / `getQueue()` must not be *called* — that only its existence, visibility
> or parameter count may be checked through `ReflectionClass` — no longer applies.** That rule
> existed because those methods reference bare constants (`PRORATE_BILLING` and friends) that
> only a live MyAdmin request defines, so calling them from a test used to fatal. The harness
> defines them first. It then executes the handlers for real, in a process of its own.
>
> A reflection-only assertion passes whether or not the thing works: `getActivate()` can exist,
> be public, be static, take one argument, and still fatal the moment it runs. Three real
> production bugs in this fleet were sitting behind assertions of exactly that shape.
>
> **Use the `plugin-contract-tests` skill** for anything touching `tests/ContractTest.php`,
> the contract inspectors, or `composer myadmin:scaffold-tests`.
>
> **Everything else in this file is still accurate and still applies** — this package's own
> classes, its API wrappers, its fixtures, its bootstrap, and the reasons certain classes must
> not be constructed. Nothing below has been removed.

# phpunit-test

## Critical

- Test files live in `tests/` and use namespace `Detain\MyAdminRaid\Tests\`
- Every test class must extend `PHPUnit\Framework\TestCase` and declare `strict_types=1`
- Use tabs for indentation — never spaces (`.scrutinizer.yml` enforces `use_tabs: true`)
- No closing PHP tag (`?>`) at end of file
- For methods that call `$GLOBALS`, DB functions, or `function_requirements()`, use **source-file static analysis** (read file contents and assert strings) — never try to invoke those paths directly
- For methods that accept a subject via `$event->getSubject()`, use an **anonymous class stub** — never mock vendor classes

## Instructions

1. **Create the test file** at `tests/PluginTest.php`. Verify `tests/` exists before writing.

2. **Add the file header** — exact boilerplate:
   ```php
   <?php

   declare(strict_types=1);

   namespace Detain\MyAdminRaid\Tests;

   use Detain\MyAdminRaid\Plugin;
   use PHPUnit\Framework\TestCase;
   use ReflectionClass;
   use Symfony\Component\EventDispatcher\GenericEvent;
   ```
   Verify the class under test exists in `src/` before proceeding.

3. **Declare the test class** with `@coversDefaultClass`:
   ```php
   /**
    * @coversDefaultClass \Detain\MyAdminRaid\Plugin
    */
   class PluginTest extends TestCase
   {
       /** @var ReflectionClass<Plugin> */
       private ReflectionClass $reflection;

       protected function setUp(): void
       {
           parent::setUp();
           $this->reflection = new ReflectionClass(Plugin::class);
       }
   ```

4. **Write structure tests** using `ReflectionClass` — check `isStatic()`, `isPublic()`, parameter names, parameter types, and return types:
   ```php
   public function testGetHooksIsStatic(): void
   {
       $method = $this->reflection->getMethod('getHooks');
       $this->assertTrue($method->isStatic());
       $this->assertTrue($method->isPublic());
   }
   ```

5. **Write behavior tests for event-handler methods** using an anonymous class stub as the `GenericEvent` subject:
   ```php
   public function testGetRequirementsRegistersExpectedPaths(): void
   {
       $loader = new class {
           public array $requirements = [];
           public function add_requirement(string $name, string $path): void
           {
               $this->requirements[] = ['name' => $name, 'path' => $path];
           }
       };
       $event = new GenericEvent($loader);
       Plugin::getRequirements($event);
       $this->assertCount(4, $loader->requirements);
       $this->assertContains('class.Raid', array_column($loader->requirements, 'name'));
   }
   ```

6. **Write source-file static analysis tests** for any method that references globals, ACL, or lazy loaders:
   ```php
   public function testGetMenuChecksAcl(): void
   {
       $filename = $this->reflection->getFileName();
       $this->assertNotFalse($filename);
       $source = file_get_contents($filename);
       $this->assertStringContainsString("has_acl('client_billing')", $source);
   }
   ```

7. **Run the tests** to verify all pass:
   ```bash
   vendor/bin/phpunit
   ```
   Verify exit code is 0 before committing.

## Examples

**User says:** "Add a test that verifies `getSettings` doesn't throw when called with a plain object subject."

**Actions taken:**
- In `tests/PluginTest.php`, add inside the class:
  ```php
  public function testGetSettingsExecutesWithoutError(): void
  {
      $settings = new class {};
      $event = new GenericEvent($settings);
      Plugin::getSettings($event);
      $this->assertTrue(true, 'getSettings completed without error');
  }
  ```
- Run `vendor/bin/phpunit` — confirm 1 new test passes.

**Result:** New test asserts no exception is thrown, matching the no-op implementation in `src/Plugin.php:70-74`.

## Common Issues

- **`Class 'Detain\MyAdminRaid\Plugin' not found`** — autoloader not loaded. Run `composer install` first, then use `vendor/bin/phpunit` (not `php tests/PluginTest.php` directly).

- **`Call to undefined function function_requirements()`** — you are trying to invoke `getMenu` directly. Use source-file static analysis instead (Step 6) — read `$this->reflection->getFileName()` and assert on the string.

- **`TypeError: Argument 1 passed to ... must be an instance of ...`** — anonymous stub class doesn't implement the expected interface. Ensure the stub matches the method signatures called in the Plugin method (e.g., `add_requirement(string $name, string $path)`).

- **IndentationError / Scrutinizer fails on tabs** — editor silently converted tabs to spaces. Check with `cat -A tests/PluginTest.php | head -5` and confirm `^I` characters appear, not spaces.

- **`No tests executed`** — test method name doesn't start with `test`. All test methods must be named `testSomething(): void`.
