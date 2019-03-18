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

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
composer require endroid/teleporter
```

When you use Symfony, the [installer](https://github.com/endroid/installer)
makes sure that services are automatically wired. If this is not the case you
can find the configuration files in the `.install/symfony` folder.

## Usage

You can specify sections to include or exclude using ### condition ### tags. For
instance when we desire JWT we need to retrieve a token before performing an API call.

``` php
Feature: API Access
  In order to access protected resource
  As an API client
  I need to be able to connect

  Scenario: Perform API call
    {## if jwt ##}
    Given I retrieve a JWT token for user "admin"
    {## endif ##}
    And I send a GET request to "api/examples"
    Then the response should be in JSON
```

For the example given above we would perform the following call to copy the
project files including all JWT related code. If we omit the jwt parameter, the
files are copied without the sections marked for JWT.

``` bash
vendor/bin/teleport development project jwt
```

In general the teleport command requires the following parameters.

``` bash
vendor/bin/teleport <source_path> <target_path> <components>
```

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatible
changes will be kept to a minimum but be aware that these can occur. Lock
your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
