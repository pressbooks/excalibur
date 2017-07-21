<?php

class MetsSwapPackagerTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_packaging() {

		$tmp_dir = $this->createTmpDir();

		$package = new \Excalibur\Protocol\SwordV1\Packager\MetsSwap( $tmp_dir, 'test.zip' );
		$package->setCustodian( 'Custodioan' );
		$package->setType( 'http://purl.org/eprint/entityType/ScholarlyWork' );
		$package->setTitle( 'Title' );
		$package->setAbstract( 'Abstract' );
		foreach ( [ 'Creator 1', 'Creator 2' ] as $test_creator ) {
			$package->addCreator( $test_creator );
		}
		$package->setIdentifier( 'Identifier' );
		$package->setDateAvailable( '2017-07-21' );
		$package->setStatusStatement( 'http://purl.org/eprint/status/PeerReviewed' );
		$package->setCopyrightHolder( 'Copyright Holder' );
		$package->setCitation( 'Citation' );
		$package->addFile( __DIR__ . '/data/pressbooks.png', 'image/png' );
		$file = $package->create();

		$this->assertFileExists( $file );

		$zip = new \ZipArchive();
		$this->assertTrue( $zip->open( $file ) );

		$png = $zip->getFromName( 'pressbooks.png' );
		$this->assertTrue( $png !== false );
		$this->assertTrue( imagecreatefromstring( $png ) !== false );

		$meta = $zip->getFromName( 'mets.xml' );
		$this->assertTrue( $meta !== false );
		$xml = new \DOMDocument();
		$xml->loadXML( $meta );
		$this->assertTrue( $xml->schemaValidate( __DIR__ . '/data/mets.xsd' ) );

		unlink( $file );
		unlink( $tmp_dir . '/mets.xml' );
		rmdir( $tmp_dir );
	}


	/**
	 * Create a temporary directory, no trailing slash!
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function createTmpDir() {

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
}
