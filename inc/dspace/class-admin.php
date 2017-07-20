<?php

namespace Excalibur\Dspace;

use PressbooksMix\Assets;
use function \Pressbooks\Utility\getset;

class Admin {

	const SLUG = 'pb_dspace';

	const OPTION = 'pressbooks_dspace_options';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	public function add() {
		$hook = add_submenu_page(
			'pb_publish',
			__( 'Submit to DSpace', 'pressbooks-excalibur' ),
			__( 'Submit to DSpace', 'pressbooks-excalibur' ),
			'manage_options',
			self::SLUG,
			[ $this, 'display' ]
		);
	}

	public function assets( $hook ) {

		if ( $hook !== 'publish_page_' . self::SLUG ) {
			return;
		}

		$assets = new Assets( 'excalibur', 'plugin' );
		$assets->setSrcDirectory( 'assets' )->setDistDirectory( 'dist' );

		wp_enqueue_style( 'excalibur/css', $assets->getPath( 'styles/main.css' ), false, null );
		wp_enqueue_style( 'excalibur/datepicker', PB_PLUGIN_URL . 'symbionts/custom-metadata/css/jquery-ui-smoothness.css', false, null );
		wp_enqueue_script( 'excalibur/js', $assets->getPath( 'scripts/main.js' ), [ 'jquery', 'jquery-ui-datepicker' ], null );
	}


	public function display() {

		$meta = ( new \Pressbooks\Metadata() )->getMetaPost();
		$form_url = wp_nonce_url( get_admin_url( get_current_blog_id(), '/admin.php?page=' . self::SLUG ), self::SLUG );
		$latest_exports = \Pressbooks\Utility\latest_exports();

		if ( ! empty( $_POST ) && current_user_can( 'edit_posts' ) && check_admin_referer( self::SLUG ) ) {
			global $blog_id;
			wp_cache_delete( "book-inf-{$blog_id}", 'pb' );

			$deposit = new Deposit( $_POST['sac_url'], $_POST['sac_deposit_url'], $_POST['sac_u'], $_POST['sac_p'] );
			try {
				$deposit
					->connect()
					->send();
			} catch ( \Exception $e ) {
				printf( '<div id="message" class="error"><p>%s</p></div>', $e->getMessage() );
			}
		}

		$options = get_option( self::OPTION, [] );
		$book_info_url = admin_url( 'post.php?post=' . absint( $meta->ID ) . '&action=edit' );
		$metadata = \Pressbooks\Book::getBookInformation();

		$formats = [];
		if ( isset( $latest_exports['epub'] ) ) {
			$formats['epub'] = __( 'EPUB', 'pressbooks-excalibur' );
		}
		if ( isset( $latest_exports['epub3'] ) ) {
			$formats['epub3'] = __( 'EPUB 3', 'pressbooks-excalibur' );
		}
		if ( isset( $latest_exports['pdf'] ) ) {
			$formats['pdf'] = __( 'PDF', 'pressbooks-excalibur' );
		}

		$format_description = '';
		$format_disabled = false;
		if ( ! isset( $latest_exports['epub'] ) && ! isset( $latest_exports['epub3'] ) && ! isset( $latest_exports['pdf'] ) ) {
			$format_description = __( 'No export files were found in a compatible format. Please export your book as EPUB, EPUB3 or PDF ( digital distributon ) and return to this page to complete your submission.', 'pressbooks-excalibur' );
			$format_disabled = true;
		}

		?>
		<div class="wrap">
				<h2><?php _e( 'Submission Details', 'pressbooks-excalibur' ); ?></h2>
				<p><?php _e( 'This information is required to complete your DSpace submission and will be saved in case you need to resubmit your book at a later date.', 'pressbooks-excalibur' ); ?>
				<table class="form-table">
					<?php
					$this->displayTextInput( 'sac_url', getset( $options, 'sac_url', '' ), __( 'Service Document URL*', 'pressbooks-excalibur' ), '', true );
					$this->displayTextInput( 'sac_deposit_url', getset( $options, 'sac_deposit_url', '' ), __( 'Deposit URL*', 'pressbooks-excalibur' ), '', true );
					$this->displayTextInput( 'sac_u', getset( $options, 'sac_u', '' ), __( 'Username*', 'pressbooks-excalibur' ), '', true );
					$this->displayPasswordInput( 'sac_p', '', __( 'Password*', 'pressbooks-excalibur' ), '', true );
					?>
				</table>

				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Submit to DSpace', 'pressbooks-excalibur' ); ?>">
				</p>
			</form>
			<pre></pre>
		</div>
		<?php
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
			$values = explode( ', ', $value );
			$i = 0;
			foreach ( $values as $row ) {
				$rows .= sprintf(
					'<div class="row"><input type="text" name="%s[ ]" value="%s" class="%s" />%s</div>',
					$name,
					$row,
					$class,
					( $i > 0 ) ? sprintf( ' <button class="button button-small delete-row">%s</button>', __( 'Delete Row', 'pressbooks-excalibur' ) ) : ''
				);
				$i++;
			}
		} else {
			$rows = sprintf(
				'<div class="row"><input type="text" name="%s[ ]" value="" class="%s" /></div>',
				$name,
				$class,
				__( 'Delete Row', 'pressbooks-excalibur' )
			);
		}

		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td id="%s">%s<button class="button add-row">%s</button>%s</td></tr>',
			$name,
			$label,
			$name,
			$rows,
			__( 'Add Row', 'pressbooks-excalibur' ),
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
	 * @param array $values
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
				$choices .= sprintf( '<option value="%s" %s>%s</option>', $key, ( in_array( $key, $values, true ) ) ? 'selected' : '', $value );
			} else {
				$choices .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $values, $key, false ), $value );
			}
		}
		printf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><select name="%s%s" id="%s"%s%s%s aria-required="%s">%s</select>%s</td></tr>',
			$name,
			$label,
			$name,
			( $multiple ) ? '[ ]' : '',
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
