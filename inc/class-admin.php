<?php

namespace Excalibur;

use PressbooksMix\Assets;
use function Pressbooks\Utility\oxford_comma_explode;

abstract class Admin {

	/**
	 * Build some sort of form that handle POSTS
	 *
	 * @return void
	 */
	abstract public function display();

	/**
	 * @param $hook string
	 */
	public function assets( $hook ) {

		if ( $hook !== 'publish_page_' . static::SLUG ) {
			return;
		}

		$assets = new Assets( 'excalibur', 'plugin' );
		$assets->setSrcDirectory( 'assets' )->setDistDirectory( 'dist' );

		wp_enqueue_style( 'excalibur/css', $assets->getPath( 'styles/excalibur.css' ), false, null );
		wp_enqueue_style( 'excalibur/datepicker', PB_PLUGIN_URL . 'symbionts/custom-metadata/css/jquery-ui-smoothness.css', false, null );
		wp_enqueue_script( 'excalibur/js', $assets->getPath( 'scripts/excalibur.js' ), [ 'jquery', 'jquery-ui-datepicker' ], null );
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $description (optional)
	 * @param bool $required (optional)
	 * @param string $class (optional)
	 */
	public function displayTextInput( $name, $value, $label, $description = '', $required = false, $class = 'regular-text' ) {
		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><input type="text" name="%s" id="%s" value="%s" class="%s"%s aria-required="%s" />%s</td></tr>',
			$name,
			$label,
			$name,
			$name,
			$value,
			$class,
			( $required ) ? ' required' : '',
			$required,
			( $description ) ? sprintf( '<p class="description">%s</p>', $description ) : ''
		);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $description (optional)
	 * @param bool $required (optional)
	 * @param string $class (optional)
	 */
	public function displayPasswordInput( $name, $value, $label, $description = '', $required = false, $class = 'regular-text' ) {
		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><input type="password" name="%s" id="%s" value="%s" class="%s"%s aria-required="%s" />%s</td></tr>',
			$name,
			$label,
			$name,
			$name,
			$value,
			$class,
			( $required ) ? ' required' : '',
			$required,
			( $description ) ? sprintf( '<p class="description">%s</p>', $description ) : ''
		);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $description (optional)
	 * @param string $class (optional)
	 */
	public function displayTextInputRows( $name, $value, $label, $description = '', $class = 'regular-text' ) {
		$rows = '';

		if ( $value ) {
			$values = oxford_comma_explode( $value );
			$i = 0;
			foreach ( $values as $row ) {
				$rows .= sprintf(
					'<div class="row"><input type="text" name="%s[]" value="%s" class="%s" />%s</div>',
					$name,
					$row,
					$class,
					( $i > 0 ) ? sprintf( ' <button class="button button-small delete-row">%s</button>', __( 'Delete Row', 'excalibur' ) ) : ''
				);
				$i++;
			}
		} else {
			$rows = sprintf(
				'<div class="row"><input type="text" name="%s[]" value="" class="%s" /></div>',
				$name,
				$class,
				__( 'Delete Row', 'excalibur' )
			);
		}

		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td id="%s">%s<button class="button add-row">%s</button>%s</td></tr>',
			$name,
			$label,
			$name,
			$rows,
			__( 'Add Row', 'excalibur' ),
			( $description ) ? sprintf( '<p class="description">%s</p>', $description ) : ''
		);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $description (optional)
	 * @param bool $required (optional)
	 */
	public function displayTextArea( $name, $value, $label, $description = '', $required = false ) {
		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><textarea name="%s" id="%s" rows="5" cols="30"%s aria-required="%s" />%s</textarea>%s</td></tr>',
			$name,
			$label,
			$name,
			$name,
			( $required ) ? ' required' : '',
			$required,
			$value,
			( $description ) ? sprintf( '<p class="description">%s</p>', $description ) : ''
		);
	}

	/**
	 * @param string $name
	 * @param array $options
	 * @param array|string $values
	 * @param string $label
	 * @param string $description (optional)
	 * @param bool $required (optional)
	 * @param bool $multiple (optional)
	 * @param bool $disabled (optional)
	 */
	public function displaySelect( $name, $options, $values, $label, $description = '', $required = false, $multiple = false, $disabled = false ) {
		$choices = '';

		foreach ( $options as $key => $value ) {
			if ( $multiple ) {
				$choices .= sprintf( '<option value="%s" %s>%s</option>', $key, ( in_array( $key, $values, true ) ) ? "selected='selected'" : '', $value );
			} else {
				$choices .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $values, $key, false ), $value );
			}
		}
		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><select name="%s%s" id="%s"%s%s%s aria-required="%s">%s</select>%s</td></tr>',
			$name,
			$label,
			$name,
			( $multiple ) ? '[]' : '',
			$name,
			( $multiple ) ? ' multiple' : '',
			( $disabled ) ? ' disabled' : '',
			( $required ) ? ' required' : '',
			$required,
			$choices,
			( $description ) ? sprintf( '<p class="description">%s</p>', $description ) : ''
		);
	}

}
