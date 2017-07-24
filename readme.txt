=== Excalibur ===
Contributors: greatislander, conner_bw
Tags: publishing, SWORD, libraries, repositories
Requires at least: 4.8
Tested up to: 4.8
Stable tag: 0.1.0
License: GPLv2 or later, New BSD License
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Excalibur is a SWORD protocol client for Pressbooks.

== Description ==

Excalibur is a SWORD protocol client for Pressbooks, which supports submitting your book to a DSpace repository.

== Installation ==

```
composer require pressbooks/excalibur
```

Optional config:

    putenv( 'PB_SWORD_USER=dspace' );
    putenv( 'PB_SWORD_PASSWORD=dspace' );
    putenv( 'PB_SWORD_URL=https://demo.dspace.org/sword/servicedocument' );
    putenv( 'PB_SWORD_DEBUG=1' );

= Testing and Coding Standards =

    composer install
    composer test
    composer standards

= Assets =

    yarn
    yarn production


== Changelog ==

= 0.1.0 =
Initial release.

== Upgrade Notice ==

= 0.1.0 =
Initial release.

== License ==

Pressbooks code is License under GPLv2 or later.

The SWORD client library was originally written by Stuart Lewis (stuart@stuartlewis.com)
as part of a JISC funded project and is licenced under the New BSD Licence. This project contains
a modified version of that library. We acknowledge the copyright and include the original
disclaimer for those files. See the separate LICENSE file in the `inc/protocol/swordv1/` folder
for more info.
