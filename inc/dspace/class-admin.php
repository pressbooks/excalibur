<?php

namespace Excalibur\Dspace;

use function \Pressbooks\Utility\getset;

class Admin extends \Excalibur\Admin {

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

	/**
	 * Build some sort of form that handle POSTS
	 */
	public function display() {

		echo '<div class="wrap">';

		if ( ! $this->hasExports() ) {
			printf(
				'<div id="message" class="error"><p>%s</p></div></div>',
				__( 'No export files were found. Please export your book as EPUB, MOBI and/or PDF and return to this page to complete your submission.', 'pressbooks-excalibur' )
			);
			return;
		}

		$meta = ( new \Pressbooks\Metadata() )->getMetaPost();

		if ( ! empty( $_POST ) && current_user_can( 'edit_posts' ) && check_admin_referer( self::SLUG ) ) {
			// Do something with $_POST data
			global $blog_id;
			$this->saveOptions( $_POST );
			$this->updateMetadata( $meta, $_POST );
			wp_cache_delete( "book-inf-{$blog_id}", 'pb' );
			try {
				$this->postData( $_POST );
				echo '<div id="message" class="updated"><p>Success!</p></div>';
			} catch ( \Exception $e ) {
				printf( '<div id="message" class="error"><p>%s</p></div>', $e->getMessage() );
			}
		}

		$deposit_urls = [];
		if ( $this->hasConfig() ) {
			// Get a list of Deposit URLS
			try {
				$deposit_urls = $this->depositUrls();
			} catch ( \Exception $e ) {
				printf( '<div id="message" class="error"><p>%s</p></div>', $e->getMessage() );
			}
		}

		$form_url = wp_nonce_url( get_admin_url( get_current_blog_id(), '/admin.php?page=' . self::SLUG ), self::SLUG );
		$book_info_url = admin_url( 'post.php?post=' . absint( $meta->ID ) . '&action=edit' );
		$metadata = \Pressbooks\Book::getBookInformation();
		$options = get_option( self::OPTION, [] );

		?>
		<h1><?php _e( 'Submit to DSpace', 'pressbooks-excalibur' ); ?></h1>
		<p><?php _e( 'Pressbooks can submit your EPUB or PDF to a <a href="http://www.dspace.org/">DSpace</a> repository. Please complete the information below before submitting.', 'pressbooks-excalibur' ); ?></p>
		<h2><?php _e( 'Book Information', 'pressbooks-excalibur' ); ?></h2>
		<p><?php printf( __( 'This information comes from your book&rsquo;s <a href="%s">Book Information</a> page. Any changes made here will be saved there as well.', 'pressbooks-excalibur' ), $book_info_url ); ?></p>
		<form id="dspace-form" action="<?php echo $form_url; ?>" method="POST">
			<table class="form-table">
				<?php
				// SWORD: Identifier:
				$this->displayTextInput( 'sword_identifier', getset( $options, 'sword_identifier', get_site_url() ), __( 'Identifier*', 'pressbooks-excalibur' ), '', true );

				// SWORD: Title
				$this->displayTextInput( 'pb_title', $metadata['pb_title'], __( 'Title*', 'pressbooks-excalibur' ), '', true );

				// SWORD:  Custodian
				$this->displayTextInput( 'pb_author', getset( $metadata, 'pb_author', '' ), __( 'Author*', 'pressbooks-excalibur' ), '', true );

				// SWORD: Creators
				$this->displayTextInputRows( 'pb_contributing_authors', getset( $metadata, 'pb_contributing_authors', '' ), __( 'Contributing Author(s)', 'pressbooks-excalibur' ), '', 'regular-text contributing-author' );

				// SWORD: Copyright Holder
				$this->displayTextInput( 'pb_copyright_holder', getset( $metadata, 'pb_copyright_holder', '' ), __( 'Copyright Holder', 'pressbooks-excalibur' ) );

				$this->displayTextInput( 'pb_publisher', getset( $metadata, 'pb_publisher', '' ), __( 'Publisher', 'pressbooks-excalibur' ), null, false );

				// SWORD: Date Available
				$this->displayTextInput( 'pb_publication_date', ( isset( $metadata['pb_publication_date'] ) ) ? strftime( '%Y-%m-%d', $metadata['pb_publication_date'] ) : '', __( 'Publication Date', 'pressbooks-excalibur' ) );

				// SWORD: Abstract
				$this->displayTextArea( 'pb_about_50', getset( $metadata, 'pb_about_50', '' ), __( 'Short Description', 'pressbooks-excalibur' ) );

				// SWORD: Citation
				$this->displayTextArea( 'sword_citation', getset( $options, 'sword_citation', '' ), __( 'Citation', 'pressbooks-excalibur' ) );

				// SWORD: Language
				$this->displaySelect( 'pb_language', \Pressbooks\L10n\supported_languages(), ( isset( $metadata['pb_language'] ) ) ? $metadata['pb_language'] : 'en', __( 'Language*', 'pressbooks-excalibur' ), '', true );

				// SWORD: Status Statement
				$status_statements = [
					'http://purl.org/eprint/status/PeerReviewed' => 'Peer reviewed',
					'http://purl.org/eprint/status/NonPeerReviewed' => 'Non-peer reviewed',
				];
				$this->displaySelect( 'sword_status_statement', $status_statements, getset( $options, 'sword_status_statement', '' ), __( 'Status Statement*', 'pressbooks-excalibur' ), '', true, false );
				?>
			</table>

			<?php if ( ! $this->hasConfig() ) { ?>
				<h2><?php _e( 'Submission Details', 'pressbooks-excalibur' ); ?></h2>
				<p><?php _e( 'This information is required to complete your DSpace submission and will be saved in case you need to resubmit your book at a later date.', 'pressbooks-excalibur' ); ?>
				<table class="form-table">
					<?php
					$this->displayTextInput( 'sword_deposit_url', getset( $options, 'sword_deposit_url', '' ), __( 'Deposit URL*', 'pressbooks-excalibur' ), '', true );
					$this->displayTextInput( 'sword_user', getset( $options, 'sword_user', '' ), __( 'Username*', 'pressbooks-excalibur' ), '', true );
					$this->displayPasswordInput( 'sword_password', '', __( 'Password*', 'pressbooks-excalibur' ), '', true );
					?>
				</table>
			<?php } elseif ( ! empty( $deposit_urls ) ) { ?>
				<table class="form-table">
					<?php
					$this->displaySelect( 'sword_deposit_url', $deposit_urls, getset( $options, 'sword_deposit_url', '' ), __( 'Deposit URL*', 'pressbooks-excalibur' ), '', true );
					?>
				</table>
			<?php } else { ?>
				<p><em><?php _e( "Configuration problem: You don't have access to any Dspace collections? Contact your system administrator.", 'pressbooks-excalibur' ) ?></em></p>
			<?php } ?>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Submit to DSpace', 'pressbooks-excalibur' ); ?>">
			</p>
		</form>
		<pre></pre>
		<?php
		echo '</div>';
	}

	/**
	 * @return bool
	 */
	protected function hasConfig() {
		if ( getenv( 'PB_SWORD_USER' ) && getenv( 'PB_SWORD_PASSWORD' ) && getenv( 'PB_SWORD_URL' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check for compatible expor types
	 *
	 * @return bool
	 */
	protected function hasExports() {
		$latest_exports = \Pressbooks\Utility\latest_exports();
		foreach ( $latest_exports as $type => $latest_export ) {
			if ( in_array( $type, Deposit::$supportedExportTypes, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param \WP_Post $meta meta post
	 * @param array $data form data
	 */
	protected function updateMetadata( $meta, $data ) {
		foreach ( $data as $key => $value ) {
			if ( strpos( $key, 'pb_' ) !== 0 ) {
				continue;
			}

			// Save Back into Pressbooks metadata
			if ( in_array( $key, [ 'pb_title', 'pb_subtitle', 'pb_author', 'pb_publisher', 'pb_about_50', 'pb_copyright_holder' ], true ) ) {
				// Strings
				$this->updateString( $meta->ID, $key, $value );
			} elseif ( in_array( $key, [ 'pb_publication_date' ], true ) ) {
				// Strings in date format
				$this->updateString( $meta->ID, $key, strtotime( $value ) );
			} elseif ( in_array( $key, [ 'pb_contributing_authors' ], true ) ) {
				// Sanitize an array of string then update it
				$this->updateArray( $meta->ID, $key, $value );
			} elseif ( in_array( $key, [ 'pb_language' ], true ) ) {
				// Select
				$this->updateSelect( $meta->ID, $key, $value );
			}
		}
	}

	/**
	 * @param array $data form data
	 */
	protected function saveOptions( $data ) {
		$option = [];
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, [ 'sword_user', 'sword_identifier', 'sword_citation' ], true ) ) {
				$option[ $key ] = sanitize_text_field( $value );
			} elseif ( in_array( $key, [ 'sword_url', 'sword_deposit_url', 'sword_status_statement' ], true ) ) {
				$option[ $key ] = esc_url_raw( $value );
			}
		}
		update_option( self::OPTION, $option );
	}

	/**
	 * @param $id
	 * @param $key
	 * @param $value
	 */
	protected function updateString( $id, $key, $value ) {
		$value = sanitize_text_field( $value );
		if ( $value !== '' ) {
			update_post_meta( $id, $key, $value );
		} else {
			delete_post_meta( $id, $key );
		}
	}

	/**
	 * @param $id
	 * @param $key
	 * @param $value
	 */
	protected function updateSelect( $id, $key, $value ) {
		update_post_meta( $id, $key, $value );
	}

	/**
	 * @param $id
	 * @param $key
	 * @param $value
	 */
	protected function updateArray( $id, $key, $value ) {
		$values = array_map( 'sanitize_text_field', $value );
		delete_post_meta( $id, $key );
		foreach ( $values as $row ) {
			if ( $row !== '' ) {
				add_post_meta( $id, $key, $row );
			}
		}
	}

	/**
	 * @param array $data form data
	 */
	protected function postData( $data ) {

		if ( $this->hasConfig() ) {
			$deposit = new Deposit(
				getenv( 'PB_SWORD_URL' ),
				getset( $data, 'sword_deposit_url' ),
				getenv( 'PB_SWORD_USER' ),
				getenv( 'PB_SWORD_PASSWORD' )
			);
		} else {
			$deposit = new Deposit(
				null,
				getset( $data, 'sword_deposit_url' ),
				getset( $data, 'sword_user' ),
				getset( $data, 'sword_password' )
			);
		}

		$deposit->buildAndSendPackage( $data );
	}

	/**
	 * @return array
	 */
	protected function depositUrls() {

		// Get deposit URLS
		$deposit = new Deposit(
			getenv( 'PB_SWORD_URL' ),
			null,
			getenv( 'PB_SWORD_USER' ),
			getenv( 'PB_SWORD_PASSWORD' )
		);

		return $deposit->queryForDepositUrls();
	}
}
