# Teleporter

*By [endroid](https://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/teleporter.svg)](https://packagist.org/packages/endroid/teleporter)
[![Build Status](http://img.shields.io/travis/endroid/teleporter.svg)](http://travis-ci.org/endroid/teleporter)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/teleporter.svg)](https://packagist.org/packages/endroid/teleporter)
[![License](http://img.shields.io/packagist/l/endroid/teleporter.svg)](https://packagist.org/packages/endroid/teleporter)

Copies files from one location to another while filtering the file contents
and directories based on a list of component names to include. This allows you
to have one single generic source for building many project variants with any
combination of components.

## Usage

You can use the provided teleport command like this.

``` bash
vendor/bin/teleport <source_path> <target_path> <components>
```

Here I use the command to generate a specific admin + api project from my
generic development folder which contains all possible project variations.

``` bash
vendor/bin/teleport development project admin api
```

All sections named different than admin and api are ignored while copying.

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
composer require endroid/teleporter
```

When you use Symfony, the [installer](https://github.com/endroid/installer)
makes sure that services are automatically wired.

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatible
changes will be kept to a minimum but be aware that these can occur. Lock
your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
