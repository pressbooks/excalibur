# Excalibur #
**Contributors:** greatislander, conner_bw  
**Tags:** publishing, SWORD, libraries, repositories  
**Requires at least:** 4.9.4  
**Tested up to:** 4.9.4  
**Pressbooks tested up to:** 5.0.0  
**Stable tag:** 0.3.1  
**License:** GPLv2 or later, New BSD License  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Excalibur is a SWORD protocol client for Pressbooks.

## Description ##

Excalibur is a SWORD protocol client for Pressbooks, which supports submitting your book to a DSpace repository.

Installing this plugin will add "Submit to DSpace" under the Publish menu.

## Installation ##

### Requirements ###

* PHP >= 7.0
* Pressbooks >= 5.0.0
* WordPress >= 4.9.4

### Installing ###

```
composer require pressbooks/excalibur
```

Or, download the latest version from the releases page and unzip it into your WordPress plugin directory): https://github.com/pressbooks/excalibur/releases

### Optional config ###

    putenv( 'PB_SWORD_USER=dspace' );
    putenv( 'PB_SWORD_PASSWORD=dspace' );
    putenv( 'PB_SWORD_URL=https://demo.dspace.org/sword/servicedocument' );
    putenv( 'PB_SWORD_DEBUG=1' );

### Testing and Coding Standards ###

    composer install
    composer test
    composer standards

### Assets ###

    yarn
    yarn production


## Changelog ##

### 0.3.1 ###
* Add release script for Travis.
* Add Pressbooks tested up to version.

### 0.3.0 ###
* Pressbooks 5 compatibility patches.

### 0.2.0 ###
* Update pressbooks/mix to 2.1.

### 0.1.0 ###
* Initial release.

## Upgrade Notice ##

### 0.1.0 ###
* Initial release.

## License ##

Pressbooks code is License under GPLv2 or later.

The SWORD client library was originally written by Stuart Lewis (stuart@stuartlewis.com)
as part of a JISC funded project and is licenced under the New BSD Licence. This project contains
a modified version of that library. We acknowledge the copyright and include the original
disclaimer for those files. See the separate LICENSE file in the `inc/protocol/swordv1/` folder
for more info.
