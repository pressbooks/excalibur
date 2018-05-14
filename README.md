# Excalibur 
**Contributors:** greatislander, conner_bw  
**Tags:** publishing, SWORD, libraries, repositories  
**Requires at least:** 4.9.5  
**Tested up to:** 4.9.5  
**Stable tag:** 0.3.3  
**License:** GPLv3 or later, New BSD License  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Excalibur is a SWORD protocol client for Pressbooks.


## Description 

[![Packagist](https://img.shields.io/packagist/v/pressbooks/excalibur.svg?style=flat-square)](https://packagist.org/packages/pressbooks/excalibur) [![GitHub release](https://img.shields.io/github/release/pressbooks/excalibur.svg?style=flat-square)](https://github.com/pressbooks/excalibur/releases) [![Travis](https://img.shields.io/travis/pressbooks/excalibur.svg?style=flat-square)](https://travis-ci.org/pressbooks/excalibur/) [![Codecov](https://img.shields.io/codecov/c/github/pressbooks/excalibur.svg?style=flat-square)](https://codecov.io/gh/pressbooks/excalibur)

Excalibur is a SWORD protocol client for Pressbooks, which supports submitting your book to a DSpace repository.

Installing this plugin will add "Submit to DSpace" under the Publish menu.


## Installation 


### Requirements 

* PHP >= 7.0
* Pressbooks >= 5.2.1
* WordPress >= 4.9.5


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

### 0.3.3 
* Special Characters being escaped multiple times [fix #12](https://github.com/pressbooks/excalibur/issues/12)


### 0.3.2 
* Update license: [991d479](https://github.com/pressbooks/excalibur/commit/991d479)
* Update Pressbooks tested up to version: [991d479](https://github.com/pressbooks/excalibur/commit/991d479)


### 0.3.1 
* Add release script for Travis.
* Add Pressbooks tested up to version.


### 0.3.0 
* Pressbooks 5 compatibility patches.


### 0.2.0 
* Update pressbooks/mix to 2.1.


### 0.1.0 
* Initial release.


## Upgrade Notice 


### 0.1.0 
* Initial release.


## License 

Pressbooks code is License under GPLv2 or later.

The SWORD client library was originally written by Stuart Lewis (stuart@stuartlewis.com)
as part of a JISC funded project and is licenced under the New BSD Licence. This project contains
a modified version of that library. We acknowledge the copyright and include the original
disclaimer for those files. See the separate LICENSE file in the `inc/protocol/swordv1/` folder
for more info.
