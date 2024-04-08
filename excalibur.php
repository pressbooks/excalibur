<?php
/*
Plugin Name: Excalibur
Plugin URI: https://github.com/pressbooks/excalibur/
GitHub Plugin URI: pressbooks/excalibur
Release Asset: true
Requires at least: 6.5
Requires Plugins: pressbooks
Description: Excalibur is a SWORD protocol client for Pressbooks.
Version: 0.7.0
Author: Pressbooks (Book Oven Inc.)
Author URI: https://pressbooks.org
Requires PHP: 8.1
Text Domain: excalibur
License: GPL v3 or later
Network: True
*/

// -------------------------------------------------------------------------------------------------------------------
// Class autoloader
// -------------------------------------------------------------------------------------------------------------------

\HM\Autoloader\register_class_path( 'Excalibur', __DIR__ . '/inc' );

// -------------------------------------------------------------------------------------------------------------------
// Requires
// -------------------------------------------------------------------------------------------------------------------

require( __DIR__ . '/inc/protocol/swordv1/namespace.php' );

// -------------------------------------------------------------------------------------------------------------------
// Hooks
// -------------------------------------------------------------------------------------------------------------------


if ( is_admin() ) {
	$p = new \Excalibur\Dspace\Admin();
}
