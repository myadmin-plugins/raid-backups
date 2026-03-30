---
name: plugin-event-hook
description: Adds a new event hook method to src/Plugin.php following the getMenu/getRequirements/getSettings static method pattern. Use when user says 'add hook', 'new event', 'register handler', or needs to extend Plugin functionality. Covers GenericEvent param, $event->getSubject(), and getHooks() registration. Do NOT use for modifying existing hooks or for creating new Plugin classes.
---
# plugin-event-hook

## Critical

- All hook methods MUST be `public static` — never instance methods.
- The only parameter is `GenericEvent $event` (fully type-hinted). No other signatures are accepted.
- Indentation: **tabs only** (enforced by `.scrutinizer.yml` `use_tabs: true`).
- No closing PHP tag at end of file.
- After adding the method, you MUST register it in `getHooks()` by uncommenting or adding its entry.
- Never add a return type declaration to hook methods (existing handlers have none — legacy convention).

## Instructions

1. **Identify the event name and subject type.**  
   Determine the event string key (e.g., `'system.settings'`, `'ui.menu'`, `'plugin.requirements'`) and what `$event->getSubject()` returns (loader, menu object, settings object, etc.).  
   Verify `src/Plugin.php` exists and `use Symfony\Component\EventDispatcher\GenericEvent;` is already imported before proceeding.

2. **Write the method body in `src/Plugin.php`.**  
   Add the method after the last existing hook method, using this exact structure (tabs for indentation):

   ```php
   	/**
   	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
   	 */
   	public static function getYourHook(GenericEvent $event)
   	{
   		$subject = $event->getSubject();
   		// your logic here
   	}
   ```

   - For a **menu** hook: assign `$menu = $event->getSubject();`, then guard with `if ($GLOBALS['tf']->ima == 'admin') { function_requirements('has_acl'); if (has_acl('client_billing')) { /* add items */ } }`
   - For a **requirements** hook: assign `$loader = $event->getSubject();`, then call `$loader->add_requirement('name', '/../vendor/detain/myadmin-raid-backups/src/File.php');`
   - For a **settings** hook: assign `$settings = $event->getSubject();` with docblock `/** @var \MyAdmin\Settings $settings **/`

   Verify the method is `public static` with exactly one parameter named `$event` before proceeding.

3. **Register the hook in `getHooks()`.**  
   In `src/Plugin.php`, update the `getHooks()` return array to include the new mapping. This step uses the event key from Step 1 and the method name from Step 2:

   ```php
   public static function getHooks()
   {
   	return [
   		'event.name' => [__CLASS__, 'getYourHook'],
   	];
   }
   ```

   Verify the array key matches the exact event string used by the dispatcher before proceeding.

4. **Add a signature test in `tests/PluginTest.php`.**  
   Follow the pattern of `testGetMenuMethodSignature()`. Use `ReflectionMethod` to assert the method is static, public, has one parameter named `event`, and its type is `GenericEvent::class`:

   ```php
   public function testGetYourHookMethodSignature(): void
   {
   	$method = $this->reflection->getMethod('getYourHook');
   	$this->assertTrue($method->isStatic());
   	$this->assertTrue($method->isPublic());
   	$params = $method->getParameters();
   	$this->assertCount(1, $params);
   	$this->assertSame('event', $params[0]->getName());
   	$type = $params[0]->getType();
   	$this->assertNotNull($type);
   	$this->assertSame(GenericEvent::class, $type->getName());
   }
   ```

   Also update `testClassDeclaresExpectedStaticMethods()` to include the new method name in the `$this->assertSame([...])` sorted array.

5. **Run tests.**  
   ```bash
   vendor/bin/phpunit
   ```
   All tests must pass before considering the task complete.

## Examples

**User says:** "Add a hook for the `plugin.activation` event that receives a loader and registers a new file."

**Actions taken:**
1. Event key: `'plugin.activation'`; subject: loader with `add_requirement()`.
2. Add to `src/Plugin.php`:
   ```php
   	/**
   	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
   	 */
   	public static function getActivation(GenericEvent $event)
   	{
   		$loader = $event->getSubject();
   		$loader->add_requirement('class.RaidActivation', '/../vendor/detain/myadmin-raid-backups/src/RaidActivation.php');
   	}
   ```
3. Update `getHooks()`:
   ```php
   return [
   	'plugin.activation' => [__CLASS__, 'getActivation'],
   ];
   ```
4. Add `testGetActivationMethodSignature()` to `tests/PluginTest.php`; update static method inventory assertion.
5. Run `vendor/bin/phpunit` — all green.

**Result:** `getActivation` is dispatched automatically when `plugin.activation` fires.

## Common Issues

- **"Call to undefined method" at runtime:** The event key in `getHooks()` doesn't match what the dispatcher fires. Check the exact string used in the calling `run_event()` call in the host app.
- **`testClassDeclaresExpectedStaticMethods` fails after adding hook:** You forgot to add the new method name to the sorted array in that test. The assertion uses `$this->assertSame(['getHooks', 'getMenu', ...sorted...], $names)` — insert the new name in alphabetical order.
- **`testGetHooksReturnsEmptyArray` fails:** That test asserts `[]` — it must be updated or removed once `getHooks()` returns real entries. Change it to assert the specific keys expected.
- **CS errors from `.scrutinizer.yml`:** Spaces were used instead of tabs. Run a tab-conversion pass; `.scrutinizer.yml` enforces `use_tabs: true`.
- **`vendor/bin/phpunit` reports "class not found":** PSR-4 autoload maps `Detain\MyAdminRaid\` to `src/`. New classes must live in `src/`; run `composer dump-autoload` if a new file was added.