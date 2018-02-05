<?php
/**
 * Plugin Name:     Excalibur
 * Plugin URI:      https://github.com/pressbooks/excalibur/
 * Description:     Excalibur is a SWORD protocol client for Pressbooks.
 * Author:          Pressbooks (Book Oven Inc.)
 * Author URI:      https://github.com/pressbooks
 * Text Domain:     excalibur
 * Domain Path:     /languages
 * GitHub Plugin URI: https://github.com/pressbooks/excalibur
 * Release Asset: true
 * Network: True
 * Version: 0.3.0
 *
 * @package Excalibur
 */

// -------------------------------------------------------------------------------------------------------------------
// Check minimum requirements
// -------------------------------------------------------------------------------------------------------------------

if ( ! function_exists( 'pb_meets_minimum_requirements' ) && ! @include_once( WP_PLUGIN_DIR . '/pressbooks/compatibility.php' ) ) { // @codingStandardsIgnoreLine
	add_action('admin_notices', function () {
		echo '<div id="message" class="error fade"><p>' . __( 'Cannot find Pressbooks install.', 'excalibur' ) . '</p></div>';
	});
	return;
} elseif ( ! pb_meets_minimum_requirements() ) {
	return;
}

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
