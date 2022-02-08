<?php

namespace Excalibur\Dspace;

use function \Pressbooks\Image\is_default_cover;
use function \Pressbooks\Utility\getset;

class Deposit {

	/**
	 * @see \Pressbooks\Utility\latest_exports
	 *
	 * @var array
	 */
	static public $supportedExportTypes = [
		'epub',
		'epub3',
		'mobi',
		'pdf',
		'print-pdf',
		'vanillawxr',
		'wxr',
		'xhtml',
	];

	/**
	 * @var \Excalibur\Protocol\SwordV1\Client
	 */
	protected $sword;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $depositUrl;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * Temporary directory used to build our Dspace file, no trailing slash!
	 *
	 * @var string
	 */
	protected $tmpDir;

	/**
	 * @param string $url
	 * @param string $deposit_url
	 * @param string $username
	 * @param string $password
	 * @param \Excalibur\Protocol\SwordV1\Client $sword (optional)
	 */
	public function __construct( $url, $deposit_url, $username, $password, $sword = null ) {

		$this->url = $url;
		$this->depositUrl = $deposit_url;
		$this->username = $username;
		$this->password = $password;

		if ( $sword ) {
			$this->sword = $sword;
		} else {
			$this->sword = new \Excalibur\Protocol\SwordV1\Client();
			$this->sword->setDebug( (bool) getenv( 'PB_SWORD_DEBUG' ) );
		}
	}

	/**
	 * Delete temporary directory when done.
	 */
	function __destruct() {
		$this->deleteTmpDir();
	}

	/**
	 * @return array()
	 *
	 * @throws \Exception
	 */
	public function queryForDepositUrls() {

		$sd = $this->sword->serviceDocument( $this->url, $this->username, $this->password );

		if ( $sd instanceof \Excalibur\Protocol\SwordV1\ErrorDocument ) {
			throw new \Exception(
				! empty( $sd->summary ) ? $sd->summary : $sd->statusMessage,
				$sd->status
			);
		}

		$deposit_urls = [];
		foreach ( $sd->workspaces as $workspace ) {
			foreach ( $workspace->collections as $collection ) {
				foreach ( $collection->acceptPackaging as $package_type => $package_version ) {
					if ( $package_type === 'http://purl.org/net/sword-types/METSDSpaceSIP' ) {
						$deposit_urls[ (string) $collection->href ] = $collection->collTitle;
					}
				}
			}
		}

		return $deposit_urls;
	}

	/**
	 * @param array $data form-data
	 *
	 * @throws \Exception
	 */
	public function buildAndSendPackage( $data ) {
		$this->swordV1( $data );
	}

	/**
	 * @param array $data form-data
	 *
	 * @throws \Exception
	 */
	protected function swordV1( $data ) {

		$this->tmpDir = $this->createTmpDir();
		$package = new \Excalibur\Protocol\SwordV1\Packager\MetsSwap( $this->tmpDir, time() . '.zip' );

		// Add files ------------------------------------------------------------------------------

		$exports = \Pressbooks\Utility\latest_exports();
		$exports_dir = \Pressbooks\Modules\Export\Export::getExportFolder();
		foreach ( self::$supportedExportTypes as $format ) {
			if ( isset( $exports[ $format ] ) ) {
				$file = untrailingslashit( $exports_dir ) . '/' . $exports[ $format ];
				$package->addFile( $file, $this->mimeType( $file ) );
			}
		}

		// Add cover ------------------------------------------------------------------------------

		$metadata = \Pressbooks\Book::getBookInformation();
		if ( ! empty( $metadata['pb_cover_image'] ) && ! is_default_cover( $metadata['pb_cover_image'] ) ) {
			$source_path = \Pressbooks\Utility\get_media_path( $metadata['pb_cover_image'] );
			$dest_path = $this->tmpDir . '/cover.' . pathinfo( $source_path, PATHINFO_EXTENSION ); // Rename image to cover.ext
			copy( $source_path, $dest_path );
			$package->addFile( $dest_path, $this->mimeType( $dest_path ) );
		}

		// Add meta --------------------------------------------------------------------------------

		// Hard code the type because the other options don't make much sense for us
		// @see http://www.ukoln.ac.uk/repositories/digirep/index/Eprints_EntityType_Vocabulary_Encoding_Scheme
		$package->setType( 'http://purl.org/eprint/entityType/ScholarlyWork' );

		// Can be either `http://purl.org/eprint/status/PeerReviewed` OR `http://purl.org/eprint/status/NonPeerReviewed`
		// @see http://www.ukoln.ac.uk/repositories/digirep/index/Eprints_Status_Vocabulary_Encoding_Scheme
		$package->setStatusStatement( getset( $data, 'sword_status_statement' ) );

		// An unambiguous reference to the resource within a given context.
		$package->setIdentifier( getset( $data, 'sword_identifier' ) );

		// Title
		$package->setTitle( getset( $data, 'pb_title' ) );

		// Long description
		$package->setAbstract( getset( $data, 'pb_about_50' ) );

		// Author
		$package->setCustodian( getset( $data, 'pb_author' ) );
		$package->addCreator( getset( $data, 'pb_author' ) );

		// Contributing authors
		foreach ( getset( $data, 'pb_contributing_authors', [] ) as $test_creator ) {
			$package->addCreator( $test_creator );
		}

		// Format: W3CDTF profile of ISO 8601
		// @see http://www.w3.org/TR/NOTE-datetime
		$package->setDateAvailable( getset( $data, 'pb_publication_date' ) );

		// A person or organization owning copyright in the resource.
		$package->setCopyrightHolder( getset( $data, 'pb_copyright_holder' ) );

		// Bibliographic Citation
		$package->setCitation( getset( $data, 'sword_citation' ) );

		// Language - two letter code
		$package->setLanguage( getset( $data, 'pb_language' ) );

		/* @codingStandardsIgnoreStart */

		// TODO: Initial testing of the following items didn't show up in DSpace?

		// Publisher
		$package->setPublisher( getset( $data, 'pb_publisher' ) );

		// Subject
		// $package->addSubject( '' );

		// A statement of any changes in ownership and custody of the resource since its creation that are significant for its authenticity, integrity, and interpretation.
		// $package->addProvenance( '' );

		// Information about rights held in and over the resource.
		// $package->addRights( '' );

		/* @codingStandardsIgnoreEnd */

		// Create file -----------------------------------------------------------------------------

		$zip = $package->create();

		// Deposit
		$response = $this->sword->deposit(
			$this->depositUrl,
			$this->username,
			$this->password,
			'',
			$zip,
			'http://purl.org/net/sword-types/METSDSpaceSIP',
			'application/zip'
		);

		if ( $response instanceof \Excalibur\Protocol\SwordV1\ErrorDocument ) {
			throw new \Exception(
				! empty( $response->summary ) ? $response->summary : $response->statusMessage,
				$response->status
			);
		}
	}

	/**
	 * Create a temporary directory, no trailing slash!
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function createTmpDir() {

		$temp_file = tempnam( sys_get_temp_dir(), '' );
		if ( file_exists( $temp_file ) ) {
			unlink( $temp_file );
		}
		mkdir( $temp_file );
		if ( ! is_dir( $temp_file ) ) {
			throw new \Exception( 'Could not create temporary directory.' );

		}

		return untrailingslashit( $temp_file );
	}

	/**
	 * Create a temporary directory
	 */
	protected function deleteTmpDir() {
		// Cleanup temporary directory, if any
		if ( ! empty( $this->tmpDir ) ) {
			$this->obliterateDir( $this->tmpDir );
		}
	}

	/**
	 * Recursively delete all contents of a directory.
	 *
	 * @param string $dirname
	 * @param bool $only_empty
	 *
	 * @return bool
	 */
	protected function obliterateDir( $dirname, $only_empty = false ) {

		if ( ! is_dir( $dirname ) ) {
			return false;
		}

		$dscan = [ realpath( $dirname ) ];
		$darr = [];
		while ( ! empty( $dscan ) ) {
			$dcur = array_pop( $dscan );
			$darr[] = $dcur;
			$d = opendir( $dcur );
			if ( $d ) {
				while ( $f = readdir( $d ) ) { // phpcs:ignore
					if ( '.' === $f || '..' === $f ) {
						continue;
					}
					$f = $dcur . '/' . $f;
					if ( is_dir( $f ) ) {
						$dscan[] = $f;
					} else {
						unlink( $f );
					}
				}
				closedir( $d );
			}
		}
		$i_until = ( $only_empty ) ? 1 : 0;
		for ( $i = count( $darr ) - 1; $i >= $i_until; $i-- ) {
			if ( ! rmdir( $darr[ $i ] ) ) {
				trigger_error( "Warning: There was a problem deleting a temporary file in $dirname", E_USER_WARNING );
			}
		}

		return ( ( $only_empty ) ? ( count( scandir( $dirname ) ) <= 2 ) : ( ! is_dir( $dirname ) ) );
	}

	/**
	 * Detect MIME Content-type for a file.
	 *
	 * @param string $file fullpath
	 *
	 * @return string
	 */
	protected function mimeType( $file ) {

		if ( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime = finfo_file( $finfo, $file );
			finfo_close( $finfo );
		} elseif ( function_exists( 'mime_content_type' ) ) {
			$mime = @mime_content_type( $file ); // Suppress deprecated message @codingStandardsIgnoreLine
		} else {
			exec( 'file -b --mime-type ' . escapeshellarg( $file ), $output );
			$mime = $output[0];
		}

		return $mime;
	}

}
