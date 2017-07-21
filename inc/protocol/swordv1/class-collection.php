<?php

namespace Excalibur\Protocol\SwordV1;

/**
 * @copyright Stuart Lewis (stuart@stuartlewis.com)
 * @license New BSD License
 */
class Collection {

	/**
	 * The title of the collection
	 *
	 * @var string
	 */
	public $collTitle;

	/**
	 * The URL of the collection (where you can deposit to)
	 *
	 * @var \SimpleXMLElement|string
	 */
	public $href;

	/**
	 * The types of content accepted
	 *
	 * @var \SimpleXMLElement|string
	 */
	public $accept;

	/**
	 * The accepted packaging formats
	 *
	 * @var array
	 */
	public $acceptPackaging;

	/**
	 * The collection policy
	 *
	 * @var string
	 */
	public $collPolicy;

	/**
	 * The colelction abstract (dcterms)
	 *
	 * @var string
	 */
	public $abstract;

	/**
	 * Whether mediation is allowed or not
	 *
	 * @var bool
	 */
	public $mediation;

	/**
	 * A nested service document
	 *
	 * @var string
	 */
	public $service;

	/**
	 * Construct a new collection by passing in a title
	 *
	 * @param string $sac_newcolltitle
	 */
	public function __construct( $sac_newcolltitle ) {
		// Store the title
		$this->collTitle = sac_clean( $sac_newcolltitle );

		// Create the accepts arrays
		$this->accept = [];
		$this->acceptPackaging = [];
	}

	/**
	 * Add a new supported packaging type
	 *
	 * @param mixed $ap
	 */
	public function addAcceptPackaging( $ap ) {
		$format = (string) $ap[0];
		$q = (string) $ap[0]['q'];
		if ( empty( $q ) ) {
			$q = '1.0';
		}
		$this->acceptPackaging[ $format ] = $q;
	}

}
