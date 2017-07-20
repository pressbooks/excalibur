<?php

namespace Excalibur\Dspace;

class Deposit {

	/**
	 * @var \Excalibur\Protocol\SwordV1\Client
	 */
	protected $sword;

	/**
	 * @var \Excalibur\Protocol\SwordV1\ServiceDocument
	 */
	protected $sd;

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
	 * @param \Swordapp\Client\SWORDAPPClient $sword (optional)
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
			$this->sword->setDebug( true );
		}
	}

	/**
	 * Delete temporary directory when done.
	 */
	function __destruct() {
		$this->deleteTmpDir();
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function connect() {
		try {
			$sd = $this->sword->serviceDocument( $this->url, $this->username, $this->password );
		} catch ( \Exception $e ) {
			$sd = new \Excalibur\Protocol\SwordV1\ServiceDocument( $this->url, 500 );
		}

		if ( $sd->status !== 200 ) {
			throw new \Exception( $sd->statusMessage );
		}

		$this->sd = $sd;

		return $this;
	}


	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function send() {

		if ( ! $this->sd ) {
			throw new \Exception( 'No connection' );
		}

		echo '<h3>Service Document</h3>';
		echo '<pre>';
		echo "Received HTTP status code: {$this->sd->status} ({$this->sd->statusMessage}), URL: {$this->url}\n";
		echo htmlentities( print_r( (array) $this->sd, true ) );
		echo '</pre>';

		$this->swordV1();

		return $this;
	}

	/**
	 *
	 */
	protected function swordV1() {
		$this->tmpDir = $this->createTmpDir();
		$package = new \Excalibur\Protocol\SwordV1\Packager\MetsSwap( $this->createTmpDir(), time() . '.zip' );

		// Add files
		$exports = \Pressbooks\Utility\latest_exports();
		$exports_dir = \Pressbooks\Modules\Export\Export::getExportFolder();
		foreach ( [ 'pdf', 'epub', 'mobi' ] as $format ) {
			if ( isset( $exports[ $format ] ) ) {
				$file = untrailingslashit( $exports_dir ) . '/' . $exports[ $format ];
				$package->addFile( $file, $this->mimeType( $file ) );
			}
		}

		// Add meta
		$test_type = 'http://purl.org/eprint/entityType/ScholarlyWork';
		$test_title = 'Pressbooks Test';
		$test_abstract = 'This is a test.';
		$test_creators = [ 'Lewis, Stuart', 'Chartrand, Dac', 'Zimmerman, Ned' ];
		$test_citation = 'Allinson, J., Francois, S., Lewis, S. SWORD: Simple Web-service Offering Repository Deposit, Ariadne, Issue 54, January 2008. Online at http://www.ariadne.ac.uk/issue54/';
		$test_identifier = 'https://pressbooks.dev/book/';
		$test_dateavailable = '2017-07';
		$test_copyrightholder = 'Stuart Lewis';
		$test_custodian = 'Stuart Lewis';
		$test_statusstatement = 'http://purl.org/eprint/status/PeerReviewed';

		$package->setCustodian( $test_custodian );
		$package->setType( $test_type );
		$package->setTitle( $test_title );
		$package->setAbstract( $test_abstract );
		foreach ( $test_creators as $test_creator ) {
			$package->addCreator( $test_creator );
		}
		$package->setIdentifier( $test_identifier );
		$package->setDateAvailable( $test_dateavailable );
		$package->setStatusStatement( $test_statusstatement );
		$package->setCopyrightHolder( $test_copyrightholder );
		$package->setCitation( $test_citation );

		// Create file
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

		echo '<h3>Deposit</h3>';
		echo '<pre>';
		echo "File: $zip\n";
		echo "Received HTTP status code: {$response->status} ({$response->statusMessage}), URL: {$this->depositUrl}\n";
		echo htmlentities( print_r( (array) $response, true ) );
		echo '</pre>';
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
	 *
	 * @throws \Exception
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
				while ( $f = readdir( $d ) ) {
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
