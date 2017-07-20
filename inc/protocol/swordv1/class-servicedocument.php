<?php

namespace Excalibur\Protocol\SwordV1;

class ServiceDocument {

	/**
	 * The URL of this Service Document
	 *
	 * @var string
	 */
	public $url;

	/**
	 * The HTTP status code returned
	 *
	 * @var int
	 */
	public $status;

	/**
	 * The XML of the service document
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
	 * The version of the SWORD server
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Whether or not verbose output is supported
	 *
	 * @var bool
	 */
	public $verbose;

	/**
	 * Whether or not the noOp command is supported
	 *
	 * @var bool
	 */
	public $noOp;

	/**
	 * The max upload size of deposits
	 *
	 * @var int
	 */
	public $maxUploadSize;

	/**
	 * Workspaces in the service document
	 *
	 * @var Workspace[]
	 */
	public $workspaces;

	/**
	 * Construct a new servicedocument by passing in the http status code
	 *
	 * @param string $sac_theurl
	 * @param int $sac_newstatus
	 * @param string $sac_thexml (optional)
	 */
	function __construct( $sac_theurl, $sac_newstatus, $sac_thexml = '' ) {

		// Store the URL
		$this->url = $sac_theurl;

		// Store the status
		$this->status = $sac_newstatus;

		// Store the raw xml
		$this->xml = $sac_thexml;
		// Store the status message
		switch ( $this->status ) {
			case 200:
				$this->statusMessage = 'OK';
				break;
			case 401:
			case 403:
				$this->statusMessage = 'Unauthorized';
				break;
			case 404:
				$this->statusMessage = 'Service document not found';
				break;
			default:
				$this->statusMessage = "Unknown error (status code {$this->status})";
				break;
		}

		// Parse the xml if there is some
		if ( ! empty( $sac_thexml ) ) {
			$sac_xml = @new \SimpleXMLElement( $sac_thexml ); // @codingStandardsIgnoreLine
			$sac_ns = $sac_xml->getNamespaces( true );
			$this->version = $sac_xml->children( $sac_ns['sword'] )->version;
			$this->verbose = $sac_xml->children( $sac_ns['sword'] )->verbose;
			$this->noOp = $sac_xml->children( $sac_ns['sword'] )->noOp;
			$this->maxUploadSize = $sac_xml->children( $sac_ns['sword'] )->maxUploadSize;

			// Build the workspaces
			$sac_ws = @$sac_xml->children( $sac_ns['app'] ); // @codingStandardsIgnoreLine
			foreach ( $sac_ws as $sac_workspace ) {
				$sac_newworkspace = new Workspace(
					$sac_workspace->children( $sac_ns['atom'] )->title
				);
				$sac_newworkspace->buildhierarchy( @$sac_workspace->children( $sac_ns['app'] ), $sac_ns ); // @codingStandardsIgnoreLine
				$this->workspaces[] = $sac_newworkspace;
			}
		}
	}
}
