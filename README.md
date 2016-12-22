Package Skeleton
================

[![Build Status](http://img.shields.io/travis/SammyK/package-skeleton.svg)](https://github.com/vampireJSV/cockpit_client)
[![Total Downloads](http://img.shields.io/packagist/dm/sammyk/package-skeleton.svg)](https://packagist.org/packages/creativados/cockpit)
[![Latest Stable Version](http://img.shields.io/packagist/v/sammyk/package-skeleton.svg)](https://packagist.org/packages/creativados/cockpit)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/vampireJSV/cockpit_client/blob/master/LICENSE)


:package_description

- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


Installation
------------

Add the package-skeleton package to your `composer.json` file.

``` json
{
    "require": {
        "creativados/cockpit": "*"
    }
}
```

Or via the command line in the root of your Laravel installation.

``` bash
$ composer require "creativados/cockpit:*"
```

Usage
-----

``` php
use SammyK\Skeleton;

$skeleton = new Skeleton();
echo $skeleton->echoPhrase('Hello, World!');

```


Testing
-------

``` bash
$ phpunit
```


Contributing
------------

Please see [CONTRIBUTING](https://github.com/vampireJSV/cockpit_client/blob/master/CONTRIBUTING.md) for details.


Credits
-------

- [Josevi canet](https://github.com/vampireJSV)
- [Special thanks Samy Kaye](https://github.com/SammyK)


License
-------

The MIT License (MIT). Please see [License File](https://github.com/SammyK/package-skeleton/blob/master/LICENSE) for more information.
