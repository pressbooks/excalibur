# Excalibur

**Contributors:** greatislander, conner_bw \
**Tags:** publishing, SWORD, libraries, repositories \
**Requires at least:** 6.0.3 \
**Tested up to:** 6.0.3 \
**Stable tag:** 0.4.0 \
**License:** GPLv3 or later, New BSD License \
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

Excalibur is a SWORD protocol client for Pressbooks.

## Description

[![Packagist](https://img.shields.io/packagist/v/pressbooks/excalibur.svg?style=flat-square)](https://packagist.org/packages/pressbooks/excalibur) [![GitHub release](https://img.shields.io/github/release/pressbooks/excalibur.svg?style=flat-square)](https://github.com/pressbooks/excalibur/releases) [![Travis](https://img.shields.io/travis/pressbooks/excalibur.svg?style=flat-square)](https://travis-ci.org/pressbooks/excalibur/) [![Codecov](https://img.shields.io/codecov/c/github/pressbooks/excalibur.svg?style=flat-square)](https://codecov.io/gh/pressbooks/excalibur)

Excalibur is a SWORD protocol client for Pressbooks, which supports submitting your book to a DSpace repository.

Installing this plugin will add "Submit to DSpace" under the Publish menu.

## Installation

### Requirements

* PHP >= 7.4
* Pressbooks >= 5.34.1
* WordPress >= 5.9.3

### Installing

```
composer require pressbooks/excalibur
```

Or, download the latest version from the releases page and unzip it into your WordPress plugin directory): https://github.com/pressbooks/excalibur/releases

### Optional config

    putenv( 'PB_SWORD_USER=dspace' );
    putenv( 'PB_SWORD_PASSWORD=dspace' );
    putenv( 'PB_SWORD_URL=https://demo.dspace.org/sword/servicedocument' );
    putenv( 'PB_SWORD_DEBUG=1' );

### Testing and Coding Standards

    composer install
    composer test
    composer standards

### Assets

    yarn
    yarn production


## Changelog

### 0.4.0
* See https://github.com/pressbooks/excalibu/releases/tag/0.4.0
* Full release history at https://github.com/pressbooks/excalibu/releases/
