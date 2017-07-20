<?php

namespace Excalibur\Protocol\SwordV1;

class Client {

	/**
	 * Default headers
	 *
	 * @var array
	 */
	private $headers = [
		'User-Agent: Pressbooks SWORD V1 Client (0.0.1) https://github.com/pressbooks',
		'Accept: application/xml',
	];

	/**
	 * Curl debug mode
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * CURLOPT_STDERR
	 *
	 * @var resource
	 */
	private $debugStderr;

	/**
	 * Client constructor.
	 */
	public function __construct() {
	}

	/**
	 * @param bool $debug
	 */
	public function setDebug( $debug ) {
		$this->debug = $debug;
	}

	/**
	 * Request a Service Document from the specified url, with the specified credentials,
	 * and on-behalf-of the specified user.
	 *
	 * @param  string $sac_url
	 * @param  string $sac_u
	 * @param  string $sac_p
	 * @param  string $sac_obo (optional)
	 *
	 * @return ServiceDocument
	 * @throws \Exception
	 */
	function serviceDocument( $sac_url, $sac_u, $sac_p, $sac_obo = '' ) {

		$sac_curl = $this->curlInit( $sac_url, $sac_u, $sac_p );

		$headers = $this->headers;
		if ( ! empty( $sac_obo ) ) {
			$headers[] = "X-On-Behalf-Of: {$sac_obo}";
		}
		curl_setopt( $sac_curl, CURLOPT_HTTPHEADER, $headers );

		$sac_resp = curl_exec( $sac_curl );
		$sac_status = curl_getinfo( $sac_curl, CURLINFO_HTTP_CODE );

		$this->curlClose( $sac_curl );

		// Parse the result
		if ( 200 === (int) $sac_status ) {
			try {
				$sac_sdresponse = new ServiceDocument( $sac_url, $sac_status, $sac_resp );
			} catch ( \Exception $e ) {
				$this->parseFailure( $e, $sac_status, $sac_resp );
				exit;
			}
		} else {
			$sac_sdresponse = new ServiceDocument( $sac_url, $sac_status );
		}
		// Return the servicedocument object
		return $sac_sdresponse;
	}

	/**
	 * Perform a deposit to the specified url, with the specified credentials,
	 * on-behalf-of the specified user, and with the given file and formatnamespace and noop setting
	 *
	 * @param  string $sac_url
	 * @param  string $sac_u
	 * @param  string $sac_p
	 * @param  string $sac_obo
	 * @param  string $sac_fname
	 * @param  string $sac_packaging (optional)
	 * @param  string $sac_contenttype (optional)
	 * @param  bool $sac_noop (optional)
	 * @param  bool $sac_verbose (optional)
	 *
	 * @return Entry|ErrorDocument
	 * @throws \Exception
	 */
	function deposit(
		$sac_url,
		$sac_u,
		$sac_p,
		$sac_obo,
		$sac_fname,
		$sac_packaging = '',
		$sac_contenttype = '',
		$sac_noop = false,
		$sac_verbose = false
	) {
		$sac_curl = $this->curlInit( $sac_url, $sac_u, $sac_p );
		curl_setopt( $sac_curl, CURLOPT_POST, true );

		$headers = $this->headers;
		$headers[] = 'Content-MD5: ' . md5_file( $sac_fname );
		if ( ! empty( $sac_obo ) ) {
			$headers[] = "X-On-Behalf-Of: {$sac_obo}";
		}
		if ( ! empty( $sac_packaging ) ) {
			$headers[] = "X-Packaging: {$sac_packaging}";
		}
		if ( ! empty( $sac_contenttype ) ) {
			$headers[] = "Content-Type: {$sac_contenttype}";
		}
		$headers[] = 'Content-Length: ' . filesize( $sac_fname );
		if ( $sac_noop === true ) {
			$headers[] = 'X-No-Op: true';
		}
		if ( $sac_verbose === true ) {
			$headers[] = 'X-Verbose: true';
		}
		$this->setContentDispositionHeaders( $headers, $sac_fname );

		curl_setopt( $sac_curl, CURLOPT_READDATA, fopen( $sac_fname, 'rb' ) );
		curl_setopt( $sac_curl, CURLOPT_HTTPHEADER, $headers );

		$sac_resp = curl_exec( $sac_curl );
		$sac_status = curl_getinfo( $sac_curl, CURLINFO_HTTP_CODE );

		$this->curlClose( $sac_curl );

		// Parse the result
		$sac_dresponse = new Entry( $sac_status, $sac_resp );
		// Was it a succesful result?
		if ( ( $sac_status >= 200 ) && ( $sac_status < 300 ) ) {
			try {
				// Get the deposit results
				$sac_xml = @new \SimpleXMLElement( $sac_resp ); // @codingStandardsIgnoreLine
				$sac_ns = $sac_xml->getNamespaces( true );
				// Build the deposit response object
				$sac_dresponse->buildHierarchy( $sac_xml, $sac_ns );
			} catch ( \Exception $e ) {
				$this->parseFailure( $e, $sac_status, $sac_resp );
				exit;
			}
		} else {
			try {
				// Parse the result
				$sac_dresponse = new ErrorDocument( $sac_status, $sac_resp );
				// Get the deposit results
				$sac_xml = @new \SimpleXMLElement( $sac_resp ); // @codingStandardsIgnoreLine
				$sac_ns = $sac_xml->getNamespaces( true );
				// Build the deposit response object
				$sac_dresponse->buildHierarchy( $sac_xml, $sac_ns );
			} catch ( \Exception $e ) {
				$this->parseFailure( $e, $sac_status, $sac_resp );
				exit;
			}
		}
		// Return the deposit object
		return $sac_dresponse;
	}


	/**
	 * Generic private method to initalise a curl transaction
	 *
	 * @param  string $sac_url
	 * @param  string $sac_user
	 * @param  string $sac_password
	 *
	 * @return resource
	 */
	private function curlInit( $sac_url, $sac_user, $sac_password ) {
		// Initialise the curl object
		$sac_curl = curl_init();

		// Return the content from curl, rather than outputting it
		curl_setopt( $sac_curl, CURLOPT_RETURNTRANSFER, true );

		// Debug
		if ( $this->debug ) {
			curl_setopt( $sac_curl, CURLOPT_VERBOSE, true );
			$this->debugStderr = fopen( 'php://temp', 'w+' );
			curl_setopt( $sac_curl, CURLOPT_STDERR, $this->debugStderr );
		}

		// Set the URL to connect to
		curl_setopt( $sac_curl, CURLOPT_URL, $sac_url );

		// If required, set authentication
		if ( ! empty( $sac_user ) && ! empty( $sac_password ) ) {
			curl_setopt( $sac_curl, CURLOPT_USERPWD, "{$sac_user}:{$sac_password}" );
		}

		// Return the initalised curl object
		return $sac_curl;
	}

	/**
	 * @param resource $sac_curl
	 */
	private function curlClose( $sac_curl ) {
		curl_close( $sac_curl );
		if ( $this->debug ) {
			rewind( $this->debugStderr );
			$verbose_log = stream_get_contents( $this->debugStderr );
			echo "<pre>### CURLOPT_STDERR ###\n", htmlspecialchars( $verbose_log ), "</pre>\n";
		}
	}

	/**
	 * @param &$headers
	 * @param string $sac_fname
	 */
	private function setContentDispositionHeaders( &$headers, $sac_fname ) {
		$index = strpos( strrev( $sac_fname ), '/' );
		if ( $index !== false ) {
			$index = strlen( $sac_fname ) - $index;
			$sac_fname_trimmed = substr( $sac_fname, $index );
		} else {
			$sac_fname_trimmed = $sac_fname;
		}
		$headers[] = "Content-Disposition: filename={$sac_fname_trimmed}";
	}

	/**
	 * @param \Exception $e
	 * @param int $sac_status
	 * @param string $sac_resp
	 *
	 * @throws \Exception
	 */
	private function parseFailure( $e, $sac_status, $sac_resp ) {
		preg_match( '#<body[^>]*>(.*?)</body>#is', $sac_resp, $matches );
		if ( ! empty( $matches[1] ) ) {
			throw new \Exception( "[{$sac_status}] " . wp_strip_all_tags( $matches[1], true ), $sac_status, $e );
		} else {
			throw new \Exception( "[{$sac_status}] Error parsing XML document (" . $e->getMessage() . ')', $sac_status, $e );
		}
	}
}
