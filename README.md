# MyAdmin RAID Backups Plugin

RAID backup management plugin for the [MyAdmin](https://github.com/detain/myadmin) hosting control panel framework. Provides event-driven hooks for RAID-based backup handling, menu integration, and ACL-controlled administration through the Symfony EventDispatcher component.

[![Tests](https://github.com/detain/myadmin-raid-backups/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-raid-backups/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-raid-backups/version)](https://packagist.org/packages/detain/myadmin-raid-backups)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-raid-backups/downloads)](https://packagist.org/packages/detain/myadmin-raid-backups)
[![License](https://poser.pugx.org/detain/myadmin-raid-backups/license)](https://packagist.org/packages/detain/myadmin-raid-backups)

## Features

- Event-driven architecture using Symfony EventDispatcher
- RAID backup requirement registration and lazy loading
- ACL-based menu integration for admin panels
- Pluggable settings and configuration hooks

## Requirements

- PHP 8.2 or higher
- ext-soap

## Installation

```sh
composer require detain/myadmin-raid-backups
```

## Usage

The plugin registers itself through the MyAdmin plugin system and provides event hooks for:

- **Menu integration** -- Adds admin menu items with ACL checks
- **Requirement loading** -- Registers RAID backup classes and functions
- **Settings** -- Provides configuration hooks for the settings system

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

Licensed under the [LGPL-2.1](https://opensource.org/licenses/LGPL-2.1) license.
