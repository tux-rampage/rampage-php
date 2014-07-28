# The Rampage PHP Framework

This framework is based on ZendFramework 2.
Its goal is to simplyfy the usage of ZF2 and offer some enhancments.

## Key Features

* Tight integration between Di and ServiceManager
* XML based module configurations (XSD provided)
* Powerful resource locators for module resource files (i.e. js and css)
* Advanced url locators/helpers
* Cascading themes support

## License

This framework is licensed under the terms of the GNU General Public License v3
You can view the license terms in the LICENSE.md file or under [http://www.gnu.org/licenses/gpl-3.0-standalone.html](http://www.gnu.org/licenses/gpl-3.0-standalone.html)

## Business Support

Support for this framework and implementations based on this framework are provided by [LUKA netconsult GmbH](http://www.luka.de/).

Feel free to contact them for:
* Hosting your solution
* Web application development and consulting
* Business SLAs

# Setup

## Manual setup

1. Grab a copy of this framework and ZF2
2. Make sure Zend\Loader\AutoloaderFactory is loaded/included, if it is not in your include path
3. Include rampage.php `require_once 'rampage.php'` (or rampage.phar if using the phar version)

## Composer

1. Add a composer dependency to rampage-php/framework: `php composer.phar require rampage-php/framework`
2. Run composer.phar install
3. Include "vendor/autoload.php" as usual.


# Contributing

Please review the contribution guide in CONTRIBUTING.md before making any contributions.


# Additional Information / FAQ

## Known issues

When using the Phar version, be aware that most php stacks of linux distributions
have problems with that when using the distribution's apc extension.

To avoid this problem install the latest apc version from pecl with `pecl install apc`.
The problem seems to be fixed in this version.

## Why configs via XML?

It is much easier to document and validate available config options as xml with a
xml schema definition (xsd) than a php array.

With the provided xsd files you will have an overview which options are available and how to
configure them correctly. The framework encapsulates the translation of the xml to the
ZF2 config array.

## Is the module's manifest.xml required?

No. It is completely up to you if you'd like to use this feature or not.

## What about performance when using XML configs

Since loading a php array is faster than processig xml and it'll benefit from bytecode caches,
there is an option to pre-compile the xml definitions of your modules to a static array definition
which is preferred when it exists.

