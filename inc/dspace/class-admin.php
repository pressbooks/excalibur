<?php

namespace Excalibur\Dspace;

use PressbooksMix\Assets;

class Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	public function add() {
		$hook = add_submenu_page(
			'pb_publish',
			__( 'Submit to Dspace', 'pressbooks-excalibur' ),
			__( 'Submit to Dspace', 'pressbooks-excalibur' ),
			'manage_options',
			'pb_dspace',
			[ $this, 'display' ]
		);
	}

	public function assets( $hook ) {

		if ( $hook !== 'publish_page_pb_dspace' ) {
			return;
		}

		$assets = new Assets( 'excalibur', 'plugin' );
		$assets->setSrcDirectory( 'assets' )->setDistDirectory( 'dist' );

		wp_enqueue_style( 'excalibur/css', $assets->getPath( 'styles/main.css' ), false, null );
		wp_enqueue_style( 'excalibur/datepicker', PB_PLUGIN_URL . 'symbionts/custom-metadata/css/jquery-ui-smoothness.css', false, null );
		wp_enqueue_script( 'excalibur/js', $assets->getPath( 'scripts/main.js' ), [ 'jquery', 'jquery-ui-datepicker' ], null );
	}


	public function display() {
		// TODO
	}
}