<?php

namespace Excalibur\Protocol\SwordV1;

class Entry {

	/**
	 * The HTTP status code returned
	 *
	 * @var int
	 */
	public $status;

	/**
	 * The XML returned by the deposit
	 *
	 * @var string
	 */
	public $xml;

	/**
	 * The human readable status code
	 *
	 * @var string
	 */
	public $statusMessage;

	/**
	 * The atom:id identifier
	 *
	 * @var string
	 */
	public $id;

	/**
	 * atom:content values
	 *
	 * @var string
	 */
	public $contentSrc;

	/**
	 * atom:content values
	 *
	 * @var string
	 */
	public $contentType;

	/**
	 * The authors
	 *
	 * @var array
	 */
	public $authors;

	/**
	 * The contributors
	 *
	 * @var array
	 */
	public $contributors;

	/**
	 * The links
	 *
	 * @var array
	 */
	public $links;

	/**
	 * The title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The summary
	 *
	 * @var string
	 */
	public $summary;

	/**
	 * The rights
	 *
	 * @var string
	 */
	public $rights;

	/**
	 * The update date
	 *
	 * @var $updated
	 */
	public $updated;

	/**
	 * The packaging format used
	 *
	 * @var string $packaging
	 */
	public $packaging;

	/**
	 * The generator
	 *
	 * @var string
	 */
	public $generator;

	/**
	 * The generator uri
	 *
	 * @var string
	 */
	public $generator_uri;

	/**
	 * The user agent
	 *
	 * @var string
	 */
	public $useragent;

	/**
	 * The noOp status
	 *
	 * @var bool
	 */
	public $noOp;

	/**
	 * Construct a new deposit response by passing in the http status code
	 *
	 * @param int $sac_newstatus
	 * @param string $sac_thexml
	 */
	function __construct( $sac_newstatus, $sac_thexml ) {
		// Store the status
		$this->status = $sac_newstatus;
		// Store the xml
		$this->xml = $sac_thexml;
		// Store the status message
		switch ( $this->status ) {
			case 201:
				$this->statusMessage = 'Created';
				break;
			case 202:
				$this->statusMessage = 'Accepted';
				break;
			case 401:
				$this->statusMessage = 'Unauthorized';
				break;
			case 412:
				$this->statusMessage = 'Precondition failed';
				break;
			case 413:
				$this->statusMessage = 'Request entity too large';
				break;
			case 415:
				$this->statusMessage = 'Unsupported media type';
				break;
			default:
				$this->statusMessage = "Unknown error (status code {$this->status})";
				break;
		}

		// Initialize arrays
		$this->authors = [];
		$this->contributors = [];
		$this->links = [];

		// Assume noOp is false unless we change it later
		$this->noOp = false;
	}

	/**
	 * Build the workspace hierarchy
	 *
	 * @param \SimpleXMLElement $sac_dr
	 * @param array $sac_ns
	 */
	function buildHierarchy( $sac_dr, $sac_ns ) {
		// Set the default namespace
		$sac_dr->registerXPathNamespace( 'atom', 'http://www.w3.org/2005/Atom' );

		// Parse the results
		$this->id = $sac_dr->children( $sac_ns['atom'] )->id;
		$sac_contentbits = $sac_dr->xpath( 'atom:content' );
		if ( ! empty( $sac_contentbits ) ) {
			$this->contentSrc = $sac_contentbits[0]['src'];
			$this->contentType = $sac_contentbits[0]['type'];
		}
		// Store the authors
		foreach ( $sac_dr->children( $sac_ns['atom'] )->author as $sac_author ) {
			$sac_theauthor = (string) $sac_author->children( $sac_ns['atom'] )->name;
			$this->authors[] = $sac_theauthor;
		}

		// Store the contributors
		foreach ( $sac_dr->children( $sac_ns['atom'] )->contributor as $sac_contributor ) {
			$sac_thecontributor = (string) $sac_contributor->children( $sac_ns['atom'] )->name;
			$this->contributors[] = $sac_thecontributor;
		}

		// Store the links
		foreach ( $sac_dr->xpath( 'atom:link' ) as $sac_link ) {
			$this->links[] = sac_clean( $sac_link[0]['href'] );
		}
		// Store the title and summary
		$this->title = sac_clean( $sac_dr->children( $sac_ns['atom'] )->title );
		$this->summary = sac_clean( $sac_dr->children( $sac_ns['atom'] )->summary );
		// Store the updated date
		$this->updated = $sac_dr->children( $sac_ns['atom'] )->updated;
		// Store the rights
		$this->rights = sac_clean( $sac_dr->children( $sac_ns['atom'] )->rights );
		// Store the format namespace
		$this->packaging = $sac_dr->children( $sac_ns['sword'] )->packaging;
		// Store the generator
		$this->generator = sac_clean( $sac_dr->children( $sac_ns['atom'] )->generator );
		$sac_gen = $sac_dr->xpath( 'atom:generator' );
		if ( ! empty( $sac_gen ) ) {
			$this->generator_uri = $sac_gen[0]['uri'];
		}
		// Store the user agent
		$this->useragent = sac_clean( $sac_dr->children( $sac_ns['sword'] )->userAgent );
		// Store the noOp status
		if ( strtolower( (string) $sac_dr->children( $sac_ns['sword'] )->noOp ) === 'true' ) {
			$this->noOp = true;
		}
	}

}
