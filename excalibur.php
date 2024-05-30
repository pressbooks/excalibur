<?php
/**
 * Plugin Name: Excalibur
 * Plugin URI: https://github.com/pressbooks/excalibur/
 * GitHub Plugin URI: pressbooks/excalibur
 * Release Asset: true
 * Requires at least: WordPress 6.5
 * Requires Plugins: pressbooks
 * Description: Excalibur is a SWORD protocol client for Pressbooks.
 * x-release-please-start-version
 * Version: 0.8.1
 * x-release-please-end
 * Author: Pressbooks (Book Oven Inc.)
 * Author URI: https://pressbooks.org
 * Requires PHP: 8.1
 * Text Domain: excalibur
 * License: GPL v3 or later
 * Network: True
 *
 * @package Excalibur
 * @author Pressbooks (Book Oven Inc.)
 * @license GPL-3.0-or-later
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
