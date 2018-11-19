<?php

namespace Excalibur\Dspace;

use function Pressbooks\Utility\getset;
use function Pressbooks\Utility\oxford_comma;
use Pressbooks\Book;
use Pressbooks\Contributors;
use Pressbooks\Metadata;

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
			__( 'Submit to DSpace', 'excalibur' ),
			__( 'Submit to DSpace', 'excalibur' ),
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
				__( 'No export files were found. Please export your book as EPUB, MOBI and/or PDF and return to this page to complete your submission.', 'excalibur' )
			);
			return;
		}

		$post = $_POST; // @codingStandardsIgnoreLine
		if ( function_exists( 'wp_magic_quotes' ) ) {
			$post = stripslashes_deep( $post );
		}
		$metapost = ( new Metadata() )->getMetaPost();
		if ( ! empty( $post ) && current_user_can( 'edit_posts' ) && check_admin_referer( self::SLUG ) ) {
			$this->saveOptions( $post );
			try {
				$this->post( $post );
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
		$book_info_url = admin_url( 'post.php?post=' . absint( $metapost->ID ) . '&action=edit' );
		$options = get_option( self::OPTION, [] );
		$metadata = $this->overrideMetadataWithOptions( Book::getBookInformation(), $options );

		?>
		<h1><?php _e( 'Submit to DSpace', 'excalibur' ); ?></h1>
		<p><?php _e( 'Pressbooks can submit your EPUB or PDF to a <a href="http://www.dspace.org/">DSpace</a> repository. Please complete the information below before submitting.', 'excalibur' ); ?></p>
		<h2><?php _e( 'Book Information', 'excalibur' ); ?></h2>
		<p>
			<?php
			/* translators: %s: Book information URL */
			printf( __( 'This information comes from your book&rsquo;s <a href="%s">Book Information</a> page. (<strong>IMPORTANT NOTE:</strong> Changes you make here will <strong>NOT</strong> be reflected on the Book Information page.)', 'excalibur' ), $book_info_url );
			?>
		</p>
		<form id="dspace-form" action="<?php echo $form_url; ?>" method="POST">
			<table class="form-table">
				<?php

				// We mix deprecated contributor slugs with new contributor slugs when generating the HTML form, i.e.
				// { Left side: } Deprecated contributor slugs. Comes from 'pressbooks_dspace_options'
				// { Right side: } New contributor slugs. Comes from \Pressbooks\Book::getBookInformation()
				// Brave person from the future! You should probably rewrite this entire class. Enjoy?

				// SWORD: Identifier:
				$this->displayTextInput( 'sword_identifier', getset( $options, 'sword_identifier', get_site_url() ), __( 'Identifier*', 'excalibur' ), '', true );

				// SWORD: Title
				$this->displayTextInput( 'pb_title', $metadata['pb_title'], __( 'Title*', 'excalibur' ), '', true );

				// SWORD:  Custodian
				$this->displayTextInput( 'pb_author', getset( $metadata, 'pb_authors', '' ), __( 'Author*', 'excalibur' ), '', true );

				// SWORD: Creators
				$this->displayTextInputRows( 'pb_contributing_authors', getset( $metadata, 'pb_contributors', '' ), __( 'Contributing Author(s)', 'excalibur' ), '', 'regular-text contributing-author' );

				// SWORD: Copyright Holder
				$this->displayTextInput( 'pb_copyright_holder', getset( $metadata, 'pb_copyright_holder', '' ), __( 'Copyright Holder', 'excalibur' ) );

				$this->displayTextInput( 'pb_publisher', getset( $metadata, 'pb_publisher', '' ), __( 'Publisher', 'excalibur' ), null, false );

				// SWORD: Date Available
				$this->displayTextInput( 'pb_publication_date', ( ! empty( $metadata['pb_publication_date'] ) ) ? strftime( '%Y-%m-%d', $metadata['pb_publication_date'] ) : '', __( 'Publication Date', 'excalibur' ) );

				// SWORD: Abstract
				$this->displayTextArea( 'pb_about_50', getset( $metadata, 'pb_about_50', '' ), __( 'Short Description', 'excalibur' ) );

				// SWORD: Citation
				$this->displayTextArea( 'sword_citation', getset( $options, 'sword_citation', '' ), __( 'Citation', 'excalibur' ) );

				// SWORD: Language
				$this->displaySelect( 'pb_language', \Pressbooks\L10n\supported_languages(), ( ! empty( $metadata['pb_language'] ) ) ? $metadata['pb_language'] : 'en', __( 'Language*', 'excalibur' ), '', true );

				// SWORD: Status Statement
				$status_statements = [
					'http://purl.org/eprint/status/PeerReviewed' => 'Peer reviewed',
					'http://purl.org/eprint/status/NonPeerReviewed' => 'Non-peer reviewed',
				];
				$this->displaySelect( 'sword_status_statement', $status_statements, getset( $options, 'sword_status_statement', '' ), __( 'Status Statement*', 'excalibur' ), '', true, false );
				?>
			</table>

			<?php if ( ! $this->hasConfig() ) { ?>
				<h2><?php _e( 'Submission Details', 'excalibur' ); ?></h2>
				<p><?php _e( 'This information is required to complete your DSpace submission and will be saved in case you need to resubmit your book at a later date.', 'excalibur' ); ?>
				<table class="form-table">
					<?php
					$this->displayTextInput( 'sword_deposit_url', getset( $options, 'sword_deposit_url', '' ), __( 'Deposit URL*', 'excalibur' ), '', true );
					$this->displayTextInput( 'sword_user', getset( $options, 'sword_user', '' ), __( 'Username*', 'excalibur' ), '', true );
					$this->displayPasswordInput( 'sword_password', '', __( 'Password*', 'excalibur' ), '', true );
					?>
				</table>
			<?php } elseif ( ! empty( $deposit_urls ) ) { ?>
				<table class="form-table">
					<?php
					$this->displaySelect( 'sword_deposit_url', $deposit_urls, getset( $options, 'sword_deposit_url', '' ), __( 'Deposit URL*', 'excalibur' ), '', true );
					?>
				</table>
			<?php } else { ?>
				<p><em><?php _e( "Configuration problem: You don't have access to any Dspace collections? Contact your system administrator.", 'excalibur' ) ?></em></p>
			<?php } ?>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Submit to DSpace', 'excalibur' ); ?>">
			</p>
		</form>
		<pre></pre>
		<?php
		echo '</div>';
	}

	/**
	 * Use options over metadata
	 *
	 * @param array $metadata
	 * @param array $options
	 *
	 * @return array
	 */
	public function overrideMetadataWithOptions( $metadata, $options ) {
		$contributors = new Contributors();
		foreach ( $options as $key => $val ) {
			if ( strpos( $key, 'pb_' ) !== 0 || in_array( $key, $contributors->valid, true ) ) {
				continue; // Don't use
			} elseif ( in_array( $key, $contributors->deprecated, true ) ) {
				$metadata[ $contributors->maybeUpgradeSlug( $key ) ] = is_array( $val ) ? oxford_comma( $val ) : $val;
			} elseif ( is_array( $val ) ) {
				$metadata[ $key ] = implode( ', ', $val );
			} else {
				$metadata[ $key ] = $val;
			}
		}
		return $metadata;
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
	 * Save DSpace specific options, not back mapped Pressbooks metadata
	 *
	 * @param array $data form-data
	 */
	protected function saveOptions( $data ) {
		$option = [];
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, [ 'sword_user', 'sword_identifier', 'sword_citation' ], true ) ) {
				// Strings
				$option[ $key ] = sanitize_text_field( $value );
			} elseif ( in_array( $key, [ 'sword_url', 'sword_deposit_url', 'sword_status_statement' ], true ) ) {
				// URLs
				$option[ $key ] = esc_url_raw( $value );
			} elseif ( strpos( $key, 'pb_' ) === 0 ) {
				if ( in_array( $key, [ 'pb_title', 'pb_subtitle', 'pb_author', 'pb_publisher', 'pb_about_50', 'pb_copyright_holder' ], true ) ) {
					// Strings
					$option[ $key ] = sanitize_text_field( $value );
				} elseif ( in_array( $key, [ 'pb_publication_date' ], true ) ) {
					// Strings in date format
					$option[ $key ] = sanitize_text_field( strtotime( $value ) );
				} elseif ( in_array( $key, [ 'pb_contributing_authors' ], true ) ) {
					// Array of strings
					$option[ $key ] = array_map( 'sanitize_text_field', $value );
				} elseif ( in_array( $key, [ 'pb_language' ], true ) ) {
					// Trusted?
					$option[ $key ] = $value;
				}
			}
		}
		update_option( self::OPTION, $option );
	}

	/**
	 * @param array $data form-data
	 *
	 * @throws \Exception
	 */
	protected function post( $data ) {

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
	 * @throws \Exception
	 *
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
