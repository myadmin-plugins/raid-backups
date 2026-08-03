<?php

/**
 * Test doubles that let the Plugin's event handlers be EXECUTED rather than grepped.
 *
 * The framework helpers Plugin::getMenu() calls are unqualified, so PHP resolves them
 * against the Detain\MyAdminRaid namespace before falling back to global scope. Defining
 * them here therefore intercepts them for the code under test without shadowing the real
 * global function_requirements() that detain/myadmin-plugin-installer installs.
 *
 * Every definition is guarded so this file is safe to include from several test classes.
 */

namespace Detain\MyAdminRaid\Tests {
    /**
     * Mutable state shared with the stubs defined below.
     */
    final class FrameworkState
    {
        /** Value returned by \MyAdmin\App::ima(). */
        public static string $ima = 'client';

        /** @var array<string, bool> ACL name => granted */
        public static array $acls = [];

        /** @var list<string> every name passed to function_requirements() */
        public static array $requirements = [];

        /** @var list<string> every ACL name has_acl() was asked about */
        public static array $aclChecks = [];

        public static function reset(): void
        {
            self::$ima = 'client';
            self::$acls = [];
            self::$requirements = [];
            self::$aclChecks = [];
        }
    }

    /**
     * Recording stand-in for the admin menu the ui.menu event carries as its subject.
     */
    final class RecordingMenu
    {
        /** @var list<array<int, mixed>> every entry added to the menu */
        public array $entries = [];

        /**
         * @param mixed ...$args
         * @return void
         */
        public function add_link(...$args)
        {
            $this->entries[] = $args;
        }

        /**
         * @param mixed ...$args
         * @return void
         */
        public function add_menu(...$args)
        {
            $this->entries[] = $args;
        }
    }
}

namespace MyAdmin {
    if (!\class_exists(App::class, false)) {
        /**
         * Minimal stand-in for \MyAdmin\App exposing only the statics this plugin uses.
         */
        class App
        {
            /** @return string */
            public static function ima()
            {
                return \Detain\MyAdminRaid\Tests\FrameworkState::$ima;
            }

            /**
             * Backs the real global function_requirements() should it be reached.
             *
             * @param string|array $function
             * @return bool
             */
            public static function functionRequirements($function)
            {
                if (!\is_array($function)) {
                    \Detain\MyAdminRaid\Tests\FrameworkState::$requirements[] = (string) $function;
                }
                return true;
            }
        }
    }
}

namespace Detain\MyAdminRaid {
    use Detain\MyAdminRaid\Tests\FrameworkState;

    if (!\function_exists(__NAMESPACE__.'\function_requirements')) {
        /**
         * @param string|array $function
         * @return bool
         */
        function function_requirements($function)
        {
            if (!\is_array($function)) {
                FrameworkState::$requirements[] = (string) $function;
            }
            return true;
        }
    }

    if (!\function_exists(__NAMESPACE__.'\has_acl')) {
        /**
         * @param string $acl
         * @return bool
         */
        function has_acl($acl)
        {
            FrameworkState::$aclChecks[] = (string) $acl;
            return FrameworkState::$acls[$acl] ?? false;
        }
    }
}
