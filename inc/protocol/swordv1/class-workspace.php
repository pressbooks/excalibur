<?php

namespace Excalibur\Protocol\SwordV1;

/**
 * @copyright Stuart Lewis (stuart@stuartlewis.com)
 * @license New BSD License
 */
class Workspace {

	/**
	 * The title of the workspace
	 *
	 * @var string
	 */
	public $workspaceTitle;

	/**
	 *  Collections in the workspace
	 *
	 * @var Collection[]
	 */
	public $collections;

	/**
	 * Construct a new workspace by passing in a title
	 *
	 * @param string $sac_newworkspacetitle
	 */
	public function __construct( $sac_newworkspacetitle ) {
		// Store the title
		$this->workspaceTitle = $sac_newworkspacetitle;
	}

	/**
	 * Build the collection hierarchy
	 *
	 * @param \SimpleXMLElement[] $sac_colls
	 * @param array $sac_ns
	 */
	public function buildhierarchy( $sac_colls, $sac_ns ) {
		// Build the collections
		foreach ( $sac_colls as $sac_collection ) {
			// Create the new collection object
			$sac_newcollection = new Collection( sac_clean( $sac_collection->children( $sac_ns['atom'] )->title ) );

			// The location of the service document
			$href = $sac_collection->xpath( '@href' );
			$sac_newcollection->href = $href[0]['href'];

			// An array of the accepted deposit types
			foreach ( $sac_collection->accept as $sac_accept ) {
				$sac_newcollection->accept[] = $sac_accept;
			}
			// An array of the accepted packages
			foreach ( $sac_collection->xpath( 'sword:acceptPackaging' ) as $sac_acceptpackaging ) {
				$sac_newcollection->addAcceptPackaging( $sac_acceptpackaging[0] );
			}
			// Add the collection policy
			$sac_newcollection->collPolicy = sac_clean( $sac_collection->children( $sac_ns['sword'] )->collectionPolicy );

			// Add the collection abstract
			// Check if dcterms is in the known namspaces. If not, might not be an abstract
			if ( array_key_exists( 'dcterms', $sac_ns ) ) {
				$sac_newcollection->abstract = sac_clean( $sac_collection->children( $sac_ns['dcterms'] )->abstract );
			}
			// Find out if mediation is allowed
			if ( (string) $sac_collection->children( $sac_ns['sword'] )->mediation === 'true' ) {
				$sac_newcollection->mediation = true;
			} else {
				$sac_newcollection->mediation = false;
			}

			// Add a nested service document if there is one
			$sac_newcollection->service = sac_clean( $sac_collection->children( $sac_ns['sword'] )->service );
			// Add to the  collections in this workspace
			$this->collections[] = $sac_newcollection;
		}
	}

	/**
	 * Print debug info to screen
	 */
	public function debug() {
		echo '<pre>';
		echo htmlentities( print_r( (array) $this, true ) );
		echo '</pre>';
	}
}
