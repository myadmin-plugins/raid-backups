<?php

declare(strict_types=1);

namespace Detain\MyAdminRaid\Tests;

use Detain\MyAdminRaid\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Test suite for the Detain\MyAdminRaid\Plugin class.
 *
 * Covers class structure, static properties, pure methods,
 * event handler signatures, and static analysis of DB-dependent code.
 *
 * @coversDefaultClass \Detain\MyAdminRaid\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflection;

    /**
     * Set up the reflection instance before each test.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/Stubs.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    // ---------------------------------------------------------------
    // Class structure tests
    // ---------------------------------------------------------------

    /**
     * Test that the Plugin class exists and is instantiable.
     *
     * @covers ::__construct
     * @return void
     */
    public function testClassIsInstantiable(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Test that the class resides in the correct namespace.
     *
     * @return void
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\MyAdminRaid', $this->reflection->getNamespaceName());
    }

    /**
     * Test that the class is not abstract and not an interface.
     *
     * @return void
     */
    public function testClassIsConcreteAndNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
        $this->assertFalse($this->reflection->isInterface());
        $this->assertFalse($this->reflection->isTrait());
    }

    /**
     * Test that the constructor accepts zero parameters.
     *
     * @covers ::__construct
     * @return void
     */
    public function testConstructorHasNoRequiredParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    // ---------------------------------------------------------------
    // Static property tests
    // ---------------------------------------------------------------

    /**
     * Test that the $name static property exists and has the expected value.
     *
     * @return void
     */
    public function testStaticPropertyName(): void
    {
        $this->assertTrue($this->reflection->hasProperty('name'));
        $prop = $this->reflection->getProperty('name');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('Raid Plugin', Plugin::$name);
    }

    /**
     * Test that the $description static property exists and has the expected value.
     *
     * @return void
     */
    public function testStaticPropertyDescription(): void
    {
        $this->assertTrue($this->reflection->hasProperty('description'));
        $prop = $this->reflection->getProperty('description');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('Allows handling of Raid based Backups', Plugin::$description);
    }

    /**
     * Test that the $help static property exists and is an empty string.
     *
     * @return void
     */
    public function testStaticPropertyHelp(): void
    {
        $this->assertTrue($this->reflection->hasProperty('help'));
        $prop = $this->reflection->getProperty('help');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Test that the $type static property exists and equals 'plugin'.
     *
     * @return void
     */
    public function testStaticPropertyType(): void
    {
        $this->assertTrue($this->reflection->hasProperty('type'));
        $prop = $this->reflection->getProperty('type');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Test that the class declares exactly the expected static properties.
     *
     * @return void
     */
    public function testStaticPropertyCount(): void
    {
        $staticProps = array_filter(
            $this->reflection->getProperties(),
            static fn(\ReflectionProperty $p): bool => $p->isStatic()
        );
        $names = array_map(
            static fn(\ReflectionProperty $p): string => $p->getName(),
            $staticProps
        );
        sort($names);
        $this->assertSame(['description', 'help', 'name', 'type'], $names);
    }

    // ---------------------------------------------------------------
    // getHooks() tests
    // ---------------------------------------------------------------

    /**
     * Test that getHooks returns an array.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Test that getHooks is a static method.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test that getHooks takes no parameters.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksAcceptsNoParameters(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertCount(0, $method->getParameters());
    }

    /**
     * Test that getHooks returns an empty array (all hooks are commented out).
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksReturnsEmptyArray(): void
    {
        $this->assertSame([], Plugin::getHooks());
    }

    /**
     * Test that getHooks is idempotent (multiple calls yield the same result).
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksIsIdempotent(): void
    {
        $first = Plugin::getHooks();
        $second = Plugin::getHooks();
        $this->assertSame($first, $second);
    }

    // ---------------------------------------------------------------
    // Event handler signature tests
    // ---------------------------------------------------------------

    /**
     * Test that getMenu is a public static method accepting a GenericEvent.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getRequirements is a public static method accepting a GenericEvent.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testGetRequirementsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getSettings is a public static method accepting a GenericEvent.
     *
     * @covers ::getSettings
     * @return void
     */
    public function testGetSettingsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that all event handlers have void return (no explicit return type, returns null).
     *
     * @return void
     */
    public function testEventHandlersReturnVoid(): void
    {
        foreach (['getMenu', 'getRequirements', 'getSettings'] as $methodName) {
            $method = $this->reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            // These methods have no return type declaration (legacy code)
            // which is acceptable; we verify they exist as callable handlers.
            $this->assertTrue(
                $returnType === null || $returnType->getName() === 'void',
                "Method {$methodName} should return void or have no return type"
            );
        }
    }

    // ---------------------------------------------------------------
    // getRequirements behavior test (using anonymous class stub)
    // ---------------------------------------------------------------

    /**
     * Test that every source getRequirements() registers is a file that exists on disk.
     *
     * This replaces testGetRequirementsRegistersExpectedPaths() and
     * testGetRequirementsPathsReferenceVendorDirectory(), both of which have been
     * deleted. Those tests only ever read the registration table back out and checked
     * the names and the shape of the strings in it; neither one touched the filesystem.
     * So they stayed green for years while all four registrations
     * (class.Raid, deactivate_kcare, deactivate_abuse, get_abuse_licenses) pointed at
     * src/Raid.php and src/abuse.inc.php -- files that have never existed in this
     * package. They were a lock on the bug rather than a check on the behaviour, which
     * is why they were removed instead of adjusted.
     *
     * function_requirements() resolves a registered source as INCLUDE_ROOT.'/'.$source
     * and require_once's it, so a source with no file behind it is a fatal, not a
     * miss. Asserting existence is therefore the property that actually matters.
     *
     * getRequirements() now registers nothing, so this passes vacuously -- that is the
     * correct state for a package that ships only Plugin.php, and the test will start
     * doing real work the moment a registration is added back.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testEveryRegisteredRequirementSourceExistsOnDisk(): void
    {
        $loader = new class {
            /** @var array<int, array{name: string, path: string}> */
            public array $requirements = [];

            /**
             * @param string $name
             * @param string $path
             * @return void
             */
            public function add_requirement(string $name, string $path): void
            {
                $this->requirements[] = ['name' => $name, 'path' => $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $packageRoot = dirname(__DIR__);
        $marker = '/vendor/detain/myadmin-raid-backups';
        $missing = [];

        foreach ($loader->requirements as $req) {
            $inPackage = strstr($req['path'], $marker);
            if ($inPackage !== false) {
                // Path points inside this package; resolve it against the checkout so
                // the test works both installed under vendor/ and standalone.
                $resolved = $packageRoot.substr($inPackage, strlen($marker));
            } else {
                // Anything else resolves the way function_requirements() does it.
                $resolved = dirname($packageRoot, 3).'/include/'.$req['path'];
            }

            if (!is_file($resolved)) {
                $missing[] = "{$req['name']} => {$req['path']} (looked for {$resolved})";
            }
        }

        $this->assertSame(
            [],
            $missing,
            'every registered requirement source must resolve to a file that exists'
        );
    }

    // ---------------------------------------------------------------
    // getSettings behavior test
    // ---------------------------------------------------------------

    /**
     * Test that getSettings accepts a GenericEvent and executes without error.
     *
     * The current implementation is a no-op (retrieves subject but does nothing),
     * so we verify it completes without throwing.
     *
     * @covers ::getSettings
     * @return void
     */
    public function testGetSettingsExecutesWithoutError(): void
    {
        $settings = new class {
        };

        $event = new GenericEvent($settings);

        // Should not throw
        Plugin::getSettings($event);
        $this->assertTrue(true, 'getSettings completed without error');
    }

    // ---------------------------------------------------------------
    // Method inventory test
    // ---------------------------------------------------------------

    /**
     * Test that the class declares the expected public methods.
     *
     * @return void
     */
    public function testClassDeclaresExpectedMethods(): void
    {
        $expected = ['__construct', 'getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        $declared = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        foreach ($expected as $method) {
            $this->assertContains($method, $declared, "Class should declare method: {$method}");
        }
    }

    /**
     * Test that static methods declared in the class are exactly the expected ones.
     *
     * @return void
     */
    public function testClassDeclaresExpectedStaticMethods(): void
    {
        $staticMethods = array_filter(
            $this->reflection->getMethods(),
            static fn(\ReflectionMethod $m): bool => $m->isStatic()
                && $m->getDeclaringClass()->getName() === Plugin::class
        );
        $names = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            $staticMethods
        );
        sort($names);
        $this->assertSame(['getHooks', 'getMenu', 'getRequirements', 'getSettings'], $names);
    }

    // ---------------------------------------------------------------
    // Static analysis: source file structure
    // ---------------------------------------------------------------

    /**
     * Test that the source file uses the correct namespace declaration.
     *
     * @return void
     */
    public function testSourceFileHasCorrectNamespace(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        $this->assertStringContainsString('namespace Detain\MyAdminRaid;', $source);
    }

    /**
     * Test that the source file imports GenericEvent from Symfony.
     *
     * @return void
     */
    public function testSourceFileImportsGenericEvent(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        $this->assertStringContainsString(
            'use Symfony\Component\EventDispatcher\GenericEvent;',
            $source
        );
    }

    /**
     * Test that the source file has a valid PHP opening tag.
     *
     * @return void
     */
    public function testSourceFileHasPhpOpeningTag(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        $this->assertStringStartsWith('<?php', $source);
    }

    // ---------------------------------------------------------------
    // getMenu behaviour: the admin role gate
    // ---------------------------------------------------------------

    /**
     * Test that getMenu only consults the ACL layer for an admin session.
     *
     * This replaces testGetMenuReferencesGlobals(), which asserted that the literal
     * text "$GLOBALS['tf']->ima" appeared in the source. That pinned an implementation
     * detail — which accessor reads the current role — rather than any behaviour, so it
     * broke when the plugin migrated to \MyAdmin\App::ima() with the gate untouched, and
     * it would have stayed green had the gate been deleted but the string left in a
     * comment. Executing the handler asserts the gate itself: a client session must not
     * reach the privileged ACL lookup at all.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuDoesNotConsultAclForNonAdmin(): void
    {
        FrameworkState::reset();
        FrameworkState::$ima = 'client';
        FrameworkState::$acls = ['client_billing' => true];
        $menu = new RecordingMenu();

        Plugin::getMenu(new GenericEvent($menu));

        $this->assertSame(
            [],
            FrameworkState::$aclChecks,
            'a client session must not reach the privileged ACL lookup'
        );
        $this->assertSame(
            [],
            FrameworkState::$requirements,
            'a client session must not even lazy-load the ACL helper'
        );
        $this->assertSame([], $menu->entries, 'a client session must get no menu entries from this plugin');
    }

    /**
     * Test that an admin session reaches the ACL lookup, and lazy-loads has_acl first.
     *
     * Loading the helper before calling it is load-bearing: has_acl() is not defined
     * until function_requirements('has_acl') pulls it in, so getting that order wrong
     * would fatal instead of denying.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuConsultsBillingAclForAdminAfterLazyLoadingIt(): void
    {
        FrameworkState::reset();
        FrameworkState::$ima = 'admin';
        FrameworkState::$acls = ['client_billing' => true];
        $menu = new RecordingMenu();

        Plugin::getMenu(new GenericEvent($menu));

        $this->assertSame(['has_acl'], FrameworkState::$requirements);
        $this->assertSame(['client_billing'], FrameworkState::$aclChecks);
    }

    /**
     * Test that getMenu contributes no menu entries even for a fully privileged admin.
     *
     * The body of the has_acl('client_billing') branch is currently empty, so this
     * plugin adds nothing to the menu for anyone. Asserting that keeps the fact visible:
     * if entries are ever added, this test must be updated deliberately rather than the
     * addition slipping in unnoticed.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuAddsNoEntriesEvenForPrivilegedAdmin(): void
    {
        FrameworkState::reset();
        FrameworkState::$ima = 'admin';
        FrameworkState::$acls = ['client_billing' => true];
        $menu = new RecordingMenu();

        Plugin::getMenu(new GenericEvent($menu));

        $this->assertSame([], $menu->entries);
    }

    /**
     * Test that getMenu is not wired to any event, so it never runs in production.
     *
     * Both of this plugin's registrations (system.settings and ui.menu) are commented
     * out in getHooks(), which makes the whole plugin inert: nothing it declares is
     * dispatched. That is why the getMenu behaviour above is unreachable at runtime.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetMenuIsNotWiredToAnyHook(): void
    {
        $callbacks = array_values(Plugin::getHooks());

        $this->assertSame([], $callbacks, 'this plugin registers no listeners at all');
        foreach ($callbacks as $callback) {
            $this->assertNotSame('getMenu', is_array($callback) ? ($callback[1] ?? null) : null);
        }
    }

    /**
     * Test that getMenu references function_requirements for lazy loading.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuUsesLazyLoading(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        $this->assertStringContainsString('function_requirements(', $source);
    }

    /**
     * Test that getMenu checks has_acl for authorization.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuChecksAcl(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        $this->assertStringContainsString("has_acl('client_billing')", $source);
    }

    /**
     * Test that getMenu retrieves the event subject (menu).
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuCallsGetSubjectOnEvent(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);
        $source = file_get_contents($filename);
        // Verify the method accesses $event->getSubject()
        $this->assertMatchesRegularExpression(
            '/\$event->getSubject\(\)/',
            $source
        );
    }
}
