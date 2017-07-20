<?php

namespace Excalibur\Protocol\SwordV1;

/**
 * @copyright Stuart Lewis (stuart@stuartlewis.com)
 * @license New BSD License
 */
class ErrorDocument extends Entry {

	/**
	 * The error URI
	 *
	 * @var string
	 */
	public $errorUri;

	/**
	 * Construct a new deposit response by passing in the http status code
	 *
	 * @param int $sac_newstatus
	 * @param string $sac_thexml
	 */
	public function __construct( $sac_newstatus, $sac_thexml ) {
		// Call the super constructor
		parent::__construct( $sac_newstatus, $sac_thexml );
	}

	/**
	 * Build the error document hierarchy
	 *
	 * @param \SimpleXMLElement $sac_dr
	 * @param array $sac_ns
	 */
	public function buildHierarchy( $sac_dr, $sac_ns ) {

		// Call the super version
		parent::buildHierarchy( $sac_dr, $sac_ns );

		foreach ( $sac_dr->attributes() as $key => $value ) {
			if ( $key === 'href' ) {
				$this->errorUri = (string) $value;
				break;
			}
		}
		if ( ! $this->errorUri ) {
			$this->errorUri = (string) $sac_dr->attributes()->href;
		}
	}

}
